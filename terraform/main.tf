locals {
  service_name = "scfprocessing"
}

# Existing App Service Plan
data "azurerm_service_plan" "existing" {
  name                = var.service_plan_name
  resource_group_name = var.service_plan_rg_name
}

# Existing MySQL Flexible Server
data "azurerm_mysql_flexible_server" "existing" {
  name                = var.mysql_flexible_server_name
  resource_group_name = var.mysql_flexible_server_rg_name
}

# Existing log analytics workspace
data "azurerm_log_analytics_workspace" "existing" {
  name                = var.log_analytics_workspace_name
  resource_group_name = var.log_analytics_workspace_rg_name
}

# Resource Group
resource "azurerm_resource_group" "main" {
  name     = "${local.service_name}-rg"
  location = data.azurerm_service_plan.existing.location
}

# Production MySQL databse
resource "azurerm_mysql_flexible_database" "prod" {
  name                = "${local.service_name}_prod"
  resource_group_name = data.azurerm_mysql_flexible_server.existing.resource_group_name
  server_name         = data.azurerm_mysql_flexible_server.existing.name
  charset             = "utf8mb4"
  collation           = "utf8mb4_0900_ai_ci"
}

resource "random_password" "prod" {
  length  = 24
  special = false
}

resource "mysql_user" "prod" {
  user               = "${local.service_name}_prod"
  host               = "%"
  plaintext_password = random_password.prod.result
}

resource "mysql_grant" "prod" {
  user       = mysql_user.prod.user
  host       = mysql_user.prod.host
  database   = azurerm_mysql_flexible_database.prod.name
  privileges = ["ALL PRIVILEGES"]
}

# Stage slot MySQL database
resource "azurerm_mysql_flexible_database" "stage" {
  name                = "${local.service_name}_stage"
  resource_group_name = data.azurerm_mysql_flexible_server.existing.resource_group_name
  server_name         = data.azurerm_mysql_flexible_server.existing.name
  charset             = "utf8mb4"
  collation           = "utf8mb4_0900_ai_ci"
}

resource "random_password" "stage" {
  length  = 24
  special = false
}

resource "mysql_user" "stage" {
  user               = "${local.service_name}_stage"
  host               = "%"
  plaintext_password = random_password.stage.result
}

resource "mysql_grant" "stage" {
  user       = mysql_user.stage.user
  host       = mysql_user.stage.host
  database   = azurerm_mysql_flexible_database.stage.name
  privileges = ["ALL PRIVILEGES"]
}

resource "azurerm_application_insights" "prod" {
  name                = "${local.service_name}-prod-insights"
  resource_group_name = azurerm_resource_group.main.name
  location            = azurerm_resource_group.main.location
  application_type    = "web"
}

resource "azurerm_application_insights" "stage" {
  name                = "${local.service_name}-stage-insights"
  resource_group_name = azurerm_resource_group.main.name
  location            = azurerm_resource_group.main.location
  application_type    = "web"
}

resource "azurerm_linux_web_app"  "main" {
  name = local.service_name
  resource_group_name = azurerm_resource_group.main.name
  location = azurerm_resource_group.main.location
  service_plan_id = data.azurerm_service_plan.existing.id

  https_only = true

  site_config {
    always_on = true
    app_command_line = "./home/site/wwwroot/scripts/startup.sh"
    application_stack {
      php_version = "8.4"
    }
  }

  app_settings = {
    "CANNED_REPORTS"                        = var.canned_reports_api_key
    "SCF_REFILE"                            = var.scf_refile_api_key
    "API_KEY_INTERACTIVE"                   = var.api_key_interactive
    "GOOGLE_SHEET"                          = var.google_sheet_id
    "DB_SERVERNAME"                         = data.azurerm_mysql_flexible_server.existing.fqdn
    "DB_USERNAME"                           = mysql_user.prod.user
    "DB_PASSWORD"                           = random_password.prod.result
    "DB_DBNAME"                             = azurerm_mysql_flexible_database.prod.name
    "APPLICATIONINSIGHTS_CONNECTION_STRING" = azurerm_application_insights.prod.connection_string
  }

  sticky_settings {
    app_setting_names = [
      "DB_USERNAME",
      "DB_PASSWORD",
      "DB_DBNAME",
      "APPLICATIONINSIGHTS_CONNECTION_STRING"
    ]
  }

  logs {
    detailed_error_messages = true
    failed_request_tracing  = true
    http_logs {
      file_system {
        retention_in_days = 7
        retention_in_mb   = 50
      }
    }
  }
}

resource "azurerm_linux_web_app_slot" "stage" {
  name           = "stage"
  app_service_id = azurerm_linux_web_app.main.id

  https_only = true

  site_config {
    always_on = true
    app_command_line = "./home/site/wwwroot/scripts/startup.sh"
    application_stack {
      php_version = "8.4"
    }
  }

  app_settings = {
    "CANNED_REPORTS"                        = var.canned_reports_api_key
    "SCF_REFILE"                            = var.scf_refile_api_key
    "API_KEY_INTERACTIVE"                   = var.api_key_interactive
    "GOOGLE_SHEET"                          = var.google_sheet_id
    "DB_SERVERNAME"                         = data.azurerm_mysql_flexible_server.existing.fqdn
    "DB_USERNAME"                           = mysql_user.stage.user
    "DB_PASSWORD"                           = random_password.stage.result
    "DB_DBNAME"                             = azurerm_mysql_flexible_database.stage.name
    "APPLICATIONINSIGHTS_CONNECTION_STRING" = azurerm_application_insights.stage.connection_string
  }

  logs {
    detailed_error_messages = true
    failed_request_tracing  = true
    http_logs {
      file_system {
        retention_in_days = 7
        retention_in_mb   = 50
      }
    }
  }
}