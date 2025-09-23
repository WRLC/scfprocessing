output "linux_web_app_service_name" {
  value = azurerm_linux_web_app.main.name
}

output "linux_web_app_service_rg_name" {
  value = azurerm_linux_web_app.main.resource_group_name
}

output "linux_web_app_service_stage_slot_name" {
  value = azurerm_linux_web_app_slot.stage.name
}

output "linux_web_app_service_php_version" {
  value = azurerm_linux_web_app.main.site_config[0].application_stack[0].php_version
}