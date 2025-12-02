const hubspot = require("@hubspot/api-client");

/**
 * Função pública que recebe leads da calculadora de energia WordPress
 * e cria/atualiza contatos no HubSpot
 */
exports.main = async (context) => {
  // Verificar autenticação via API_SECRET
  const authHeader = context.request?.headers?.["x-api-key"];
  const apiSecret = context.secrets?.API_SECRET;

  if (!apiSecret || authHeader !== apiSecret) {
    return {
      statusCode: 401,
      body: JSON.stringify({
        success: false,
        message: "Unauthorized: Invalid API key",
      }),
    };
  }

  // Obter dados do body
  const body = context.request?.body;

  if (!body || !body.email) {
    return {
      statusCode: 400,
      body: JSON.stringify({
        success: false,
        message: "Email é obrigatório",
      }),
    };
  }

  try {
    // Inicializar cliente HubSpot
    const hubspotClient = new hubspot.Client({
      accessToken: context.secrets?.PRIVATE_APP_ACCESS_TOKEN,
    });

    // Preparar propriedades do contato
    const properties = {
      email: body.email,
    };

    // Mapear campos opcionais
    if (body.company_type) {
      properties.clarke_company_type = body.company_type;
    }
    if (body.company_size) {
      properties.clarke_company_size = body.company_size;
    }
    if (body.monthly_expense) {
      properties.clarke_monthly_expense = String(body.monthly_expense);
    }
    if (body.recommended_strategy) {
      properties.clarke_recommended_strategy = body.recommended_strategy;
    }
    if (body.scores) {
      properties.clarke_calculator_scores =
        typeof body.scores === "string"
          ? body.scores
          : JSON.stringify(body.scores);
    }
    if (body.all_answers) {
      properties.clarke_calculator_answers =
        typeof body.all_answers === "string"
          ? body.all_answers
          : JSON.stringify(body.all_answers);
    }

    // UTM Parameters
    if (body.utm_source) properties.hs_analytics_source = body.utm_source;
    if (body.utm_medium) properties.utm_medium = body.utm_medium;
    if (body.utm_campaign) properties.utm_campaign = body.utm_campaign;
    if (body.utm_term) properties.utm_term = body.utm_term;
    if (body.utm_content) properties.utm_content = body.utm_content;

    // Lead source
    properties.hs_lead_status = "NEW";
    properties.lifecyclestage = "lead";
    properties.clarke_lead_source = "calculadora_energia";

    // Tentar buscar contato existente
    let existingContact = null;
    try {
      const searchResponse = await hubspotClient.crm.contacts.searchApi.doSearch(
        {
          filterGroups: [
            {
              filters: [
                {
                  propertyName: "email",
                  operator: "EQ",
                  value: body.email,
                },
              ],
            },
          ],
          properties: ["email", "firstname", "lastname"],
          limit: 1,
        }
      );

      if (searchResponse.results && searchResponse.results.length > 0) {
        existingContact = searchResponse.results[0];
      }
    } catch (searchError) {
      // Contato não encontrado, vamos criar
      console.log("Contact not found, will create new one");
    }

    let contact;
    let action;

    if (existingContact) {
      // Atualizar contato existente
      contact = await hubspotClient.crm.contacts.basicApi.update(
        existingContact.id,
        { properties }
      );
      action = "updated";
    } else {
      // Criar novo contato
      contact = await hubspotClient.crm.contacts.basicApi.create({
        properties,
      });
      action = "created";
    }

    return {
      statusCode: 200,
      body: JSON.stringify({
        success: true,
        message: `Contato ${action} com sucesso`,
        contact_id: contact.id,
        action: action,
      }),
    };
  } catch (error) {
    console.error("HubSpot API Error:", error);

    return {
      statusCode: 500,
      body: JSON.stringify({
        success: false,
        message: error.message || "Erro ao processar lead",
        error: error.body || error.toString(),
      }),
    };
  }
};
