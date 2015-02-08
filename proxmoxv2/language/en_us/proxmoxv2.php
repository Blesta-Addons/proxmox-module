<?php
// Basics
$lang['Proxmoxv2.name'] = "Proxmox Reloaded";
$lang['Proxmoxv2.module_row'] = "Proxmox Master Server";
$lang['Proxmoxv2.module_rows'] = "Servers";
$lang['Proxmoxv2.module_group'] = "Proxmox Master Group";

// Errors
$lang['Proxmoxv2.!error.json.unavailable'] = "The JSON extension is required by Proxmox API .";
$lang['Proxmoxv2.!error.curl_init.unavailable'] = "Proxmox module requires that curl+ssl support is compiled into the PHP interpreter.";

$lang['Proxmoxv2.!error.server_name_valid'] = "You must enter a Server Name Label.";
$lang['Proxmoxv2.!error.user_valid'] = "Please enter a User Name.";
$lang['Proxmoxv2.!error.password_valid'] = "Please enter a Password.";
$lang['Proxmoxv2.!error.password_valid_connection'] = "Login to Proxmox Host failed. Please check to ensure that the data are correct.";
$lang['Proxmoxv2.!error.hostname_valid'] = "Please enter a Hostname.";
$lang['Proxmoxv2.!error.failed_hostname'] = "Cannot resolve  %1\$s " ;
$lang['Proxmoxv2.!error.failed_failed_port'] = "the Port %1\$s  must be between 1 and 65535. " ;
$lang['Proxmoxv2.!error.port_valid'] = "Please enter a valid port.";
$lang['Proxmoxv2.!error.PVE2_API_object'] = "Could not create PVE2_API object.";

$lang['Proxmoxv2.!error.api.internal'] = "Internal API Error returned from node";
$lang['Proxmoxv2.!error.api.internal.create'] = "Internal API When Creating The VM on the node %1\$s";
$lang['Proxmoxv2.!error.api.internal.delete'] = "Internal API When Deleting The VM on the node %1\$s";
$lang['Proxmoxv2.!error.api.internal.action'] = "Internal API When Trying to execute the action %1\$s";
$lang['Proxmoxv2.!error.api.internal.task'] = "Internal API When Trying to get task ID %1\$s  Status";
$lang['Proxmoxv2.!error.api.internal.tasks'] = "Internal API When Trying to get tasks for VM %1\$s ";


$lang['Proxmoxv2.!error.meta[type].valid'] = "Please select a valid virtualization type.";
$lang['Proxmoxv2.!error.meta[nodes].empty'] = "Please select at least one node.";
$lang['Proxmoxv2.!error.meta[memory].format'] = "Please set RAM.";
$lang['Proxmoxv2.!error.meta[cpu].format'] = "Please set vCPU count.";
$lang['Proxmoxv2.!error.meta[cpuunits].format'] = "Please set CPU UNITS.";
$lang['Proxmoxv2.!error.meta[disk].format'] = "Please set HDD size.";
$lang['Proxmoxv2.!error.meta[netspeed].format'] = "Please set NetSpeed.";
$lang['Proxmoxv2.!error.meta[set_template].format'] = "Please set whether to select a template or to allow clients to set a template.";
$lang['Proxmoxv2.!error.meta[template].empty'] = "Please select a template.";
$lang['Proxmoxv2.!error.meta[swap].format'] = "Please set SWAP.";


$lang['Proxmoxv2.!error.Proxmoxv2_hostname.format'] = "The hostname appears to be invalid.";
$lang['Proxmoxv2.!error.Proxmoxv2_template.valid'] = "Please select a valid template.";


// $lang['Proxmoxv2.!error.meta[license_type].valid'] = "Please select a valid license type.";
// $lang['Proxmoxv2.!error.meta[groupid].valid'] = "Please select a valid Group ID.";
// $lang['Proxmoxv2.!error.manage2_ipaddress.format'] = "Please enter a valid IP address.";
// $lang['Proxmoxv2.!error.no_valid_licence.for_ip'] = "There is no valid license for this ip \n.";

// Module management
$lang['Proxmoxv2.add_module_row'] = "Add Server";
$lang['Proxmoxv2.add_module_group'] = "Add Server Group";
$lang['Proxmoxv2.return_to_manage'] = "Return to Manage Page";
$lang['Proxmoxv2.manage.module_rows_title'] = "Proxmox Master Servers";
$lang['Proxmoxv2.manage.module_groups_title'] = "Proxmox Master Server Groups";
$lang['Proxmoxv2.manage.module_rows_heading.server_name'] = "Server Label";
$lang['Proxmoxv2.manage.module_rows_heading.hostname'] = "Hostname";
$lang['Proxmoxv2.manage.module_rows_heading.options'] = "Options";
$lang['Proxmoxv2.manage.module_groups_heading.name'] = "Group Name";
$lang['Proxmoxv2.manage.module_groups_heading.servers'] = "Server Count";
$lang['Proxmoxv2.manage.module_groups_heading.options'] = "Options";
$lang['Proxmoxv2.manage.module_rows.edit'] = "Edit";
$lang['Proxmoxv2.manage.module_groups.edit'] = "Edit";
$lang['Proxmoxv2.manage.module_rows.delete'] = "Delete";
$lang['Proxmoxv2.manage.module_groups.delete'] = "Delete";
$lang['Proxmoxv2.manage.module_rows.confirm_delete'] = "Are you sure you want to delete this server?";
$lang['Proxmoxv2.manage.module_groups.confirm_delete'] = "Are you sure you want to delete this server group?";
$lang['Proxmoxv2.manage.module_rows_no_results'] = "There are no servers.";
$lang['Proxmoxv2.manage.module_groups_no_results'] = "There are no server groups.";

$lang['Proxmoxv2.order_options.first'] = "First non-full server";

// Add module row
$lang['Proxmoxv2.add_row.box_title'] = "Add Proxmox Server";
$lang['Proxmoxv2.add_row.basic_title'] = "Basic Settings";
$lang['Proxmoxv2.add_row.add_btn'] = "Add Server";

// Edit module row
$lang['Proxmoxv2.edit_row.box_title'] = "Edit Proxmox Server";
$lang['Proxmoxv2.edit_row.basic_title'] = "Basic Settings";
$lang['Proxmoxv2.edit_row.edit_btn'] = "Update Server";


// Module row meta data
$lang['Proxmoxv2.row_meta.server_name'] = "Server Label";
$lang['Proxmoxv2.row_meta.realm'] = "Authentication Realms";
$lang['Proxmoxv2.row_meta.user'] = "User";
$lang['Proxmoxv2.row_meta.password'] = "Password";
$lang['Proxmoxv2.row_meta.hostname'] = "Hostname";
$lang['Proxmoxv2.row_meta.port'] = "SSL Port Number";
$lang['Proxmoxv2.row_meta.vmid'] = "Next VMID (do not change unless necessary!)";
$lang['Proxmoxv2.row_meta.storage'] = "Default storage name (e.g. local)";
$lang['Proxmoxv2.row_meta.ips'] = "IPs (one per line)";
$lang['Proxmoxv2.row_meta.default_template'] = "Default template";
$lang['Proxmoxv2.row_meta.debug'] = "Debug";

$lang['Proxmoxv2.row_meta.default_storage'] = "local";
$lang['Proxmoxv2.row_meta.default_vmid'] = "200";
$lang['Proxmoxv2.row_meta.default_port'] = "8006";

$lang['Proxmoxv2.row_meta.pam'] = "PAM - Linux PAM standard authentication";
$lang['Proxmoxv2.row_meta.pve'] = "PVE - Proxmox VE authentication server";
$lang['Proxmoxv2.row_meta.ldap'] = "LDAP - Lightweight Directory Access Protocol";
$lang['Proxmoxv2.row_meta.ad'] = "AD - Microsoft Active Directory";

// Toolstips
$lang['Proxmoxv2.!tooltip.realm'] = "Proxmox VE supports multiple authentication sources, e.g. Microsoft Active Directory, LDAP, Linux PAM or the integrated Proxmox VE authentication server";
$lang['Proxmoxv2.!tooltip.hostname'] = "Hostname Should be IP adresse or domain name without https:// ";
$lang['Proxmoxv2.!tooltip.port'] = "Don't change this if you have not changed the port in the proxmox server.";
$lang['Proxmoxv2.!tooltip.debug'] = "Activate this if you have probleme in authentificating errors.";
$lang['Proxmoxv2.!tooltip.vmid'] = " - Do not change this until you know what you are doing .";


// Package fields
$lang['Proxmoxv2.package_fields.type'] = "Type";
$lang['Proxmoxv2.package_fields.storage'] = "Default storage";
$lang['Proxmoxv2.package_fields.pool'] = "Pool";
$lang['Proxmoxv2.package_fields.onboot'] = "Auto Start";
$lang['Proxmoxv2.package_fields.disk'] = "Storage (GB)";
$lang['Proxmoxv2.package_fields.memory'] = "RAM (MB)";
$lang['Proxmoxv2.package_fields.swap'] = "SWAP (MB)";
$lang['Proxmoxv2.package_fields.cpus'] = "vCPU count";
$lang['Proxmoxv2.package_fields.cpuunits'] = "Cpu Time";
$lang['Proxmoxv2.package_fields.quotatime'] = "Quota Time";
$lang['Proxmoxv2.package_fields.quotaugidlimit'] = "Quota Ugid Limit";
$lang['Proxmoxv2.package_fields.netspeed'] = "Rate Limit (MByte/s)";

$lang['Proxmoxv2.package_fields.assigned_nodes'] = "Assigned Nodes";
$lang['Proxmoxv2.package_fields.available_nodes'] = "Available Nodes";

$lang['Proxmoxv2.package_fields.assigned_templates'] = "Assigned Templates";
$lang['Proxmoxv2.package_fields.available_templates'] = "Available Templates";

$lang['Proxmoxv2.package_fields.allow_client'] = "Allow Client To :";
$lang['Proxmoxv2.package_fields.reinstall'] = "(Re)install";
$lang['Proxmoxv2.package_fields.stats'] = "Show Stats Graph";
$lang['Proxmoxv2.package_fields.console'] = "Use Console";
$lang['Proxmoxv2.package_fields.backup'] = "Use Backup";
$lang['Proxmoxv2.package_fields.tasks'] = "Show Task History";


// Package Tooltips
$lang['Proxmoxv2.package_fields.tooltip.disk'] = "Amount of disk space for the VM in GB. A zero indicates no limits.";
$lang['Proxmoxv2.package_fields.tooltip.cpus'] = "The number of CPUs for this container.";
$lang['Proxmoxv2.package_fields.tooltip.memory'] = "Amount of RAM for the VM in MB.";
$lang['Proxmoxv2.package_fields.tooltip.swap'] = "Amount of SWAP for the VM in MB.";
$lang['Proxmoxv2.package_fields.tooltip.cpuunits'] = "CPU weight for a VM. Argument is used in the kernel fair scheduler. The larger the number is, the more CPU time this VM gets. Number is relative to weights of all the other running VMs. NOTE: You can disable fair-scheduler configuration by setting this to 0.";
$lang['Proxmoxv2.package_fields.tooltip.pool'] = "Add the VM to the specified pool.";
$lang['Proxmoxv2.package_fields.tooltip.onboot'] = "Specifies whether a VM will be started during system bootup.";
$lang['Proxmoxv2.package_fields.tooltip.quotatime'] = "Set quota grace period (seconds).";
$lang['Proxmoxv2.package_fields.tooltip.quotaugidlimit'] = "Set maximum number of user/group IDs in a container for which disk quota inside the container will be accounted. If this value is set to 0, user and group quotas inside the container will not.";

// Packages - Server types 
$lang['Proxmoxv2.types.openvz'] = "OpenVZ";
$lang['Proxmoxv2.types.kvm'] = "KVM";

// Service fields
$lang['Proxmoxv2.service_field.Proxmoxv2_hostname'] = "Hostname";
$lang['Proxmoxv2.service_field.Proxmoxv2_template'] = "Select System OS ";
$lang['Proxmoxv2.service_field.Proxmoxv2_node'] = "Select Node ";
$lang['Proxmoxv2.service_field.Proxmoxv2_vmid'] = "VM ID";

// Tabs Labels
$lang['Proxmoxv2.tab_actions']	= "Server Actions";
$lang['Proxmoxv2.tab_config']	= "VM Configuration";
$lang['Proxmoxv2.tab_network']	= "Network/DNS";
$lang['Proxmoxv2.tab_stats']	= "Stats/Graphs";
$lang['Proxmoxv2.tab_console']	= "Console";
$lang['Proxmoxv2.tab_reinstall']= "Re-install";
$lang['Proxmoxv2.tab_tasks']		= "Task History";
//next version
$lang['Proxmoxv2.tab_backup']	= "Backup";
$lang['Proxmoxv2.tab_firewall']	= "Firewall";
$lang['Proxmoxv2.tab_snapshoot']= "SnapShoot";


// Actions Tab
$lang['Proxmoxv2.tab_actions.heading_actions'] = "Vps Server Controle";
$lang['Proxmoxv2.tab_actions.server_status'] = "Server Status";
$lang['Proxmoxv2.tab_actions.status_running'] = "Running";
$lang['Proxmoxv2.tab_actions.status_stopped'] = "Stopped";
$lang['Proxmoxv2.tab_actions.status_online'] = "Online";
$lang['Proxmoxv2.tab_actions.status_offline'] = "Offline";
$lang['Proxmoxv2.tab_actions.status_disabled'] = "Disabled";
$lang['Proxmoxv2.tab_actions.actions'] = "Actions";
$lang['Proxmoxv2.tab_actions.actions.status'] = "Refresh Status";
$lang['Proxmoxv2.tab_actions.actions.start'] = "start";
$lang['Proxmoxv2.tab_actions.actions.stop'] = "stop";
$lang['Proxmoxv2.tab_actions.actions.shutdown'] = "shutdown";
$lang['Proxmoxv2.tab_actions.actions.reboot'] = "reboot";
$lang['Proxmoxv2.tab_actions.actions.change_hostname'] = "Change Hostname";
$lang['Proxmoxv2.tab_actions.actions.change_password'] = "Change Password";
$lang['Proxmoxv2.tab_actions.actions.change_config'] = "Change Settings";
$lang['Proxmoxv2.tab_actions.hostname'] = "Hostname";
$lang['Proxmoxv2.tab_actions.ip_address'] = "IP Address";
$lang['Proxmoxv2.tab_actions.uptime'] = "Uptime";
$lang['Proxmoxv2.tab_actions.description'] = "Description";
$lang['Proxmoxv2.tab_actions.cpus'] = "CPUs";
$lang['Proxmoxv2.tab_actions.nproc'] = "Number Proccess";
$lang['Proxmoxv2.tab_actions.mem'] = "Memory";
$lang['Proxmoxv2.tab_actions.cpu_usage'] = "CPU Usage";
$lang['Proxmoxv2.tab_actions.disk_usage'] = "Disk Usage";
$lang['Proxmoxv2.tab_actions.mem_usage'] = "Memory Usage";
$lang['Proxmoxv2.tab_actions.used'] = "Used";
$lang['Proxmoxv2.tab_actions.free'] = "Free";
$lang['Proxmoxv2.tab_actions.of'] = "Of";

// Config Tab
$lang['Proxmoxv2.tab_config.heading_config'] = "Vps Server Configuration";

// Stats Tab
$lang['Proxmoxv2.tab_stats.heading_stats'] = "Statistics Graphs";
$lang['Proxmoxv2.tab_stats.hour'] = "Hour";
$lang['Proxmoxv2.tab_stats.day'] = "Day";
$lang['Proxmoxv2.tab_stats.week'] = "Week";
$lang['Proxmoxv2.tab_stats.mounth'] = "Mounth";
$lang['Proxmoxv2.tab_stats.year'] = "Year";
$lang['Proxmoxv2.tab_stats.stats'] = "Graphs For ";
// next version
$lang['Proxmoxv2.tab_stats.average'] = "Average";
$lang['Proxmoxv2.tab_stats.max'] = "Max";

// Tasks Tab
$lang['Proxmoxv2.tab_tasks.heading_tasks'] = "Vps Server Tasks History";
$lang['Proxmoxv2.tab_tasks.no_tasks'] = "No task yet for this VM";
$lang['Proxmoxv2.tab_tasks.status'] = "Status";
$lang['Proxmoxv2.tab_tasks.id'] = "ID";
$lang['Proxmoxv2.tab_tasks.type'] = "Description";
$lang['Proxmoxv2.tab_tasks.pid'] = "PID";
$lang['Proxmoxv2.tab_tasks.starttime'] = "Start Time";
$lang['Proxmoxv2.tab_tasks.endtime'] = "End Time";

// Reinstall Tab
$lang['Proxmoxv2.tab_reinstall.heading_reinstall'] = "Reinstall Server";

// Console Tab
$lang['Proxmoxv2.tab_console.heading_console'] = "Console NOVNC";

// Common
$lang['Proxmoxv2.please_select'] = "-- Please Select --";
$lang['Proxmoxv2.select.yes'] = "Yes";
$lang['Proxmoxv2.select.no'] = "No";
$lang['Proxmoxv2.button.save'] = "Submit";
$lang['Proxmoxv2.button.close'] = "Close";
$lang['Proxmoxv2.not.available'] = "This Feature is not available in this version. ";
$lang['Proxmoxv2.not.available_client'] = "You can't change this setting , please contact our support for more info . ";
$lang['Proxmoxv2.activated.client_id'] = "VM Added VIA Blesta For Client ID %1\$s ";

 // Tab
// $lang['Proxmoxv2.tab_actions.heading_mount_iso'] = "Mount ISO";
// $lang['Proxmoxv2.tab_actions.heading_reinstall'] = "Reinstall";

// $lang['Proxmoxv2.tab_actions.field_iso'] = "Image";
// $lang['Proxmoxv2.tab_actions.field_mount_submit'] = "Mount";
// $lang['Proxmoxv2.tab_actions.field_template'] = "Template";
// $lang['Proxmoxv2.tab_actions.field_password'] = "Root Password";
// $lang['Proxmoxv2.tab_actions.field_reinstall_submit'] = "Reinstall";


// Client Actions Tab

// $lang['Proxmoxv2.tab_client_actions.heading_server_status'] = "Server Status";
// $lang['Proxmoxv2.tab_client_actions.status_running'] = "Online";
// $lang['Proxmoxv2.tab_client_actions.status_stopped'] = "Offline";
// $lang['Proxmoxv2.tab_client_actions.status_disabled'] = "Disabled";
// $lang['Proxmoxv2.tab_client_actions.heading_mount_iso'] = "Mount ISO";
// $lang['Proxmoxv2.tab_client_actions.heading_reinstall'] = "Reinstall";
// $lang['Proxmoxv2.tab_client_actions.field_iso'] = "Image";
// $lang['Proxmoxv2.tab_client_actions.field_mount_submit'] = "Mount";
// $lang['Proxmoxv2.tab_client_actions.field_template'] = "Template";
// $lang['Proxmoxv2.tab_client_actions.field_password'] = "Root password";
// $lang['Proxmoxv2.tab_client_actions.field_reinstall_submit'] = "Reinstall";


// Stats Tab
// $lang['Proxmoxv2.tab_stats.heading_stats'] = "Statistics";
// $lang['Proxmoxv2.tab_stats.memory'] = "Memory:";
// $lang['Proxmoxv2.tab_stats.memory_stats'] = "%1\$s/%2\$s"; // %1$s is the memory used, %2$s is the total memory available
// $lang['Proxmoxv2.tab_stats.memory_percent_available'] = "(%1\$s%%)"; // %1$s is the percentage of memory used. You MUST use two % signs to represent a single percent (i.e. %%)
// $lang['Proxmoxv2.tab_stats.heading_graphs'] = "Graphs";


// Client Stats Tab
// $lang['Proxmoxv2.tab_client_stats.heading_stats'] = "Statistics";
// $lang['Proxmoxv2.tab_client_stats.heading_graphs'] = "Graphs";


// Console Tab
// $lang['Proxmoxv2.tab_console.heading_console'] = "Console";

// $lang['Proxmoxv2.tab_console.vnc_ip'] = "VNC Host:";
// $lang['Proxmoxv2.tab_console.vnc_port'] = "VNC Port:";
// $lang['Proxmoxv2.tab_console.vnc_user'] = "VNC Username:";
// $lang['Proxmoxv2.tab_console.vnc_password'] = "VNC Password:";


// Client Console Tab
// $lang['Proxmoxv2.tab_client_console.heading_console'] = "Console";

// $lang['Proxmoxv2.tab_client_console.vnc_ip'] = "VNC Host";
// $lang['Proxmoxv2.tab_client_console.vnc_port'] = "VNC Port";
// $lang['Proxmoxv2.tab_client_console.vnc_user'] = "VNC Username";
// $lang['Proxmoxv2.tab_client_console.vnc_password'] = "VNC Password";

// $lang['Proxmoxv2.!bytes.value'] = "%1\$s%2\$s"; // %1$s is a number value, %2$s is the unit of that value (i.e., one of B, KB, MB, GB)
// $lang['Proxmoxv2.!percent.used'] = "%1\$s%"; // %1$s is a percentage value

?>