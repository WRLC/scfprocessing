variable "service_plan_name" {
  type        = string
  description = "Name of the existing app service plan"
}

variable "service_plan_rg_name" {
  type        = string
  description = "Resource group of the existing app service plan"
}

variable "mysql_flexible_server_name" {
  type        = string
  description = "Name of the existing MySQL Flexible Server to use"
}

variable "mysql_flexible_server_rg_name" {
  type        = string
  description = "Resource group of the existing MySQL Flexible Server"
}

variable "log_analytics_workspace_name" {
  type        = string
  description = "Name of the existing log analytics workspace to use"
}

variable "log_analytics_workspace_rg_name" {
  type        = string
  description = "Resource group of the existing log analytics workspace"
}

variable "mysql_admin_username" {
  type        = string
  description = "Admin username for the existing MySQL Flexible Server"
}

variable "mysql_admin_password" {
  type        = string
  description = "Admin password for the existing MySQL Flexible Server"
  sensitive   = true
}

variable "canned_reports_api_key" {
  type        = string
  description = "Alma API key for canned reports"
  sensitive   = true
}

variable "scf_refile_api_key" {
  type        = string
  description = "Alma API key for SCF refile"
  sensitive   = true
}

variable "api_key_interactive" {
  type        = string
  description = "Alma API key for interactive"
  sensitive   = true
}

variable "google_sheet_id" {
  type        = string
  description = "Google Sheet URL"
  sensitive   = true
}
