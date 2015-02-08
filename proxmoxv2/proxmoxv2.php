<?php
/**
 * Proxmoxv2 Module
 *
 */
class Proxmoxv2 extends Module {	
	
	/**
	 * Initializes the module 
	 *	 
	 * /DONE/
	 */
	public function __construct() {
		// Load components required by this module
		Loader::loadComponents($this, array("Input"));
        
		// Load config
        $this->loadConfig(dirname(__FILE__) . DS . "config.json");		
		
		// Load the language required by this module
		Language::loadLang("proxmoxv2", null, dirname(__FILE__) . DS . "language" . DS);

	}
	
    /**
	 * Performs any necessary bootstraping actions. Sets Input errors on
	 * failure, preventing the module from being added.
	 *	 
	 * /DONE/ 
	 */
	public function install() {
	
        if (!function_exists("json_encode"))
            $this->Input->setErrors(array('json' => array('unavailable' => Language::_("Proxmoxv2.!error.json.unavailable", true))));
			
        if (!function_exists('curl_init')) 
			$this->Input->setErrors(array('curl_init' => array('unavailable' => Language::_("Proxmoxv2.!error.curl_init.unavailable", true))));

	}		

//******************************//
//***  BEGIN CORE  FUNCTION  ***//
//******************************//
	
	/**
	 * Loads the JSON component into this object, making it ready to use
	 *	 
	 * /DONE/
	 */
	private function loadJson() {
		if (!isset($this->Json) || !($this->Json instanceof Json))
			Loader::loadComponents($this, array("Json"));
	}
    
	/**
	 * Initializes the BuycPanel Api and returns an instance of that object with the given account information set
	 *	 
	 * /DONE/
	 */
	private function getApi($hostname, $user,  $realm , $password , $port) {
		Loader::load(dirname(__FILE__) . DS . "apis" . DS . "pve2_api.class.php");		
		
		# Check hostname resolves.
		if (gethostbyname($hostname) == $hostname && !filter_var($hostname, FILTER_VALIDATE_IP)) {
			$this->Input->setErrors(array('proxmox' => array('resolvehost' => Language::_("Proxmoxv2.!error.failed_hostname", true , $hostname ))));
			//return $pve2 ;
		}
		
		if ($port < 1 || $port > 65535) {
			$this->Input->setErrors(array('proxmox' => array('checkport' => Language::_("Proxmoxv2.!error.failed_port", true , $port ))));			
		}

			
		$pve2 = new PVE2_API($hostname, $user,  $realm , $password , (int)$port);		
		return $pve2;
	}

	/**
	 * Send Command to API
	 *	 
	 * /DONE/
	 */
	private function SendCommand($module_row,  $command , $path , $params = null) {
	
		$pve2 = $this->getApi($module_row->meta->hostname, $module_row->meta->user, $module_row->meta->realm, $module_row->meta->password, $module_row->meta->port);
		// print_r($module_row);
		$success = false ;
		
		// if($command == "get" && isset($params) ) {
		
			// $postfields = array();
			
			// foreach($params as $arg_name => $arg_value) {
				// $postfields[] = urlencode($arg_name) . '=' . urlencode($arg_value);
			// }
			
			// $postfields = implode('&', $postfields);
			// $path = $path . '?' . $postfields ;
			// $params = null ;
		// }	
		
		$this->log($module_row->meta->hostname . "|" . $command , serialize($path ) . serialize($params) , "input", true);
		
		try {		
			// if ($pve2->constructor_success()) {
			
				// if(isset($module_row->meta->debug))
					// $pve2->set_debug(true);

				if ($pve2->login()) {
				
					$success = true ;
					$response = $pve2->{$command}($path , $params);
									
					$this->log($module_row->meta->hostname, $this->Json->encode($response)  , "output", $success);
					// print_r($response) ; 
					return $response ;
					
				} else {
					$success = false ;
					$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.password_valid_connection", true))));
					$this->log($module_row->meta->hostname, Language::_("Proxmoxv2.!error.password_valid_connection", true) , "output", $success);		
				}
			// } else {
				// $success = false ;
				// $this->Input->setErrors(array('api' => array('internal' => Language::_("Could not create PVE2_API object", true))));
				// $this->log($module_row->meta->hostname, Language::_("Could not create PVE2_API object", true) , "output", $success);			
			// }
		}
		catch (Exception $e) {
			$success = false ;
			$this->log($module_row->meta->hostname, $e->getMessage()  , "output", $success);
		}		

	}	


//******************************//
//***  ADD SERVERS SECTION   ***//
//******************************//
	
	/**
	 * Builds and returns the rules required to add/edit a module row (e.g. server)
	 *
	 * /DONE/
	 */
	private function getRowRules(&$vars) {
		
		$rules = array(
			'server_name'=>array(
				'valid'=>array(
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("Proxmoxv2.!error.server_name_valid", true)
				)
			),
			'hostname'=>array(
				'valid'=>array(
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("Proxmoxv2.!error.hostname_valid", true)
				)
			),
			'port' => array(
				'format' => array(
					'rule' => array("is_numeric") , $vars['port'] ,
					'message' => Language::_("Proxmoxv2.!error.port_valid", true)
				)
			),			
			'user'=>array(
				'valid'=>array(
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("Proxmoxv2.!error.user_valid", true)
				)
			),			
			'password'=>array(
				'valid'=>array(
					'last'=>true,
					'rule'=>"isEmpty",
					'negate'=>true,
					'message'=>Language::_("Proxmoxv2.!error.password_valid", true)
				),
				'valid_connection'=>array(
					'rule'=>array(array($this, "validateConnection"), $vars['hostname'] , $vars['port'] , $vars['realm'] , $vars['user'] ),
					'message'=>Language::_("Proxmoxv2.!error.password_valid_connection", true)
				)
			)
		);
		
		return $rules;
	}	
		
	/**
	 * Validates whether or not the connection details are valid
	 *	 
	 * /DONE/
	 */
	public function validateConnection($password , $hostname , $port , $realm , $user ) {
		// print_r($password . $hostname . $port . $user . $realm);
		try {
		
			$pve2 = $this->getApi($hostname,  $user,  $realm  ,  $password ,  $port);
			
			if ($pve2->login()) {
				// Success Login , So give it true .
				return true;
			}
		
		}
		catch (Exception $e) {
			// Trap any errors encountered, could not validate connection
			return false;
		}
		
	}	
	
	/**
	 * Returns the rendered view of the manage module page
	 *	 
	 * /DONE/
	 */
	public function manageModule($module, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("manage", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		$account_rows = count($module->rows);
		
		if ($account_rows > 0) {
			for ($i=0; $i<$account_rows; $i++) {
			
				// $this->loadJson();
				
				// $api = $this->getApi($module->rows[$i]->meta->hostname, $module->rows[$i]->meta->port, $module->rows[$i]->meta->realm, $module->rows[$i]->meta->user, $module->rows[$i]->meta->password);
				// $licenses = $api->fetchLicenses();				
				// $licenses = $this->Json->decode($this->Json->encode($licenses));
				// $module->licenses = $licenses->licenses ;
			}
		}			
		$this->view->set("module", $module);
		
		return $this->view->fetch();
	}
	
	/**
	 * Returns the rendered view of the add module row page
	 *
	 * /DONE/
	 */
	public function manageAddRow(array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("add_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();	
	}
	
	/**
	 * Returns the rendered view of the edit module row page
	 *
	 * /DONE/
	 */	
	public function manageEditRow($module_row, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("edit_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		if (empty($vars))
			$vars = $module_row->meta;
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();
	}
	
	/**
	 * Adds the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being added.
	 *
	 * /DONE/
	 */
	public function addModuleRow(array &$vars) {
		// print_r($vars);
		$meta_fields = array("server_name", "realm", "user", "password", "hostname", "port", "vmid", /* "storage", "default_template" , */ "ips", "debug");
		$encrypted_fields = array("realm" , "user", "password", "hostname");
		
		$this->Input->setRules($this->getRowRules($vars));
		
		// Validate module row
		if ($this->Input->validates($vars)) {

			// Build the meta data for this row
			$meta = array();
			foreach ($vars as $key => $value) {
				
				if (in_array($key, $meta_fields)) {
					$meta[] = array(
						'key' => $key,
						'value' => $value,
						'encrypted' => in_array($key, $encrypted_fields) ? 1 : 0
					);
				}
			}
			
			return $meta;
		}
	}
	
	/**
	 * Edits the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being updated.
	 *
	 * /DONE/
	 */
	public function editModuleRow($module_row, array &$vars) {
		// Same as adding
		return $this->addModuleRow($vars);
	}
	
	/**
	 * Deletes the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being deleted.
	 *
	 * /DONE/
	 */
	public function deleteModuleRow($module_row) {
		return null; // Nothing to do
	}	
	
	/**
	 * Returns an array of available service delegation order methods. The module
	 * will determine how each method is defined. For example, the method "first"
	 * may be implemented such that it returns the module row with the least number
	 * of services assigned to it.
	 *
	 * @return array An array of order methods in key/value pairs where the key is the type to be stored for the group and value is the name for that option
	 * @see Module::selectModuleRow()
	 *
	 * /DONE/	 
	 */
	public function getGroupOrderOptions() {
		return array('first'=>Language::_("Proxmoxv2.order_options.first", true));
	}
	
	/**
	 * Determines which module row should be attempted when a service is provisioned
	 * for the given group based upon the order method set for that group.
	 *
	 * @return int The module row ID to attempt to add the service with
	 * @see Module::getGroupOrderOptions()
	 *
	 * /DONE/	 
	 */
	public function selectModuleRow($module_group_id) {
		if (!isset($this->ModuleManager))
			Loader::loadModels($this, array("ModuleManager"));
		
		$group = $this->ModuleManager->getGroup($module_group_id);
		
		if ($group) {
			switch ($group->add_order) {
				default:
				case "first":
					
					foreach ($group->rows as $row) {
						return $row->id;
					}
					
					break;
			}
		}
		return 0;
	}	
	
//******************************//
//******  PACKAGE SECTION  *****//
//******************************//	
	
	/**
	 * Retrieves a list of rules for validating adding/editing a package
	 *
	 * /DONE/
	 */
	private function getPackageRules(array $vars = null) {
		$rules = array(
			'meta[type]' => array(
				'valid' => array(
					'rule' => array("in_array", array_keys($this->getTypes())),
					'message' => Language::_("Proxmoxv2.!error.meta[type].valid", true)
				)
			),
			'meta[nodes]' => array(
				'empty' => array(
					'rule' => array(array($this, "validateNodeSet")),
					'message' => Language::_("Proxmoxv2.!error.meta[nodes].empty", true),
				)
			),
			'meta[storage]' => array(
				'empty' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("Proxmoxv2.!error.meta[storage].empty", true)
				)
			),			
			'meta[cpus]' => array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[cpus].format", true)
				)
			),
			'meta[cpuunits]' => array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[cpuunits].format", true , "CPUUNITS" )
				)
			),	
			'meta[disk]' => array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[disk].format", true)
				)
			),			
			'meta[memory]' => array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[memory].format", true)
				)
			),
			'meta[templates]' => array(
				'empty' => array(
					'rule' => array(array($this, "validateTempaltesSet")),
					'message' => Language::_("Proxmoxv2.!error.meta[templates].empty", true),
				)
			)			
		);	
			
		
		if ($vars['meta']['type'] == "openvz") {			
			$rules['meta[swap]'] = array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[swap].format", true , "SWAP")
				)
			);
		}
		if ($vars['meta']['type'] == "qemu") {
			$rules['meta[netspeed]'] = array(
				'format' => array(
					'rule' => array("matches", "/^[0-9]+$/"),
					'message' => Language::_("Proxmoxv2.!error.meta[netspeed].format", true)
				)
			);				
		}			
		
		return $rules;
	}
	
	/**
	 * Validates input data when attempting to add a package, returns the meta
	 * data to save when adding a package. Performs any action required to add
	 * the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being added.
	 *
	 * /DONE/
	 */
	public function addPackage(array $vars=null) {
		$this->Input->setRules($this->getPackageRules($vars));
		
		$meta = array();
		if ($this->Input->validates($vars)) {
			// Return all package meta fields
			foreach ($vars['meta'] as $key => $value) {
				$meta[] = array(
					'key' => $key,
					'value' => $value,
					'encrypted' => 0
				);
			}
		}
		
		return $meta;
	}

	/**
	 * Validates input data when attempting to edit a package, returns the meta
	 * data to save when editing a package. Performs any action required to edit
	 * the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being edited.
	 *
	 * /DONE/
	 */
	public function editPackage($package, array $vars=null) {
		$this->Input->setRules($this->getPackageRules($vars));
		
		$meta = array();
		if ($this->Input->validates($vars)) {
			// Return all package meta fields
			foreach ($vars['meta'] as $key => $value) {
				$meta[] = array(
					'key' => $key,
					'value' => $value,
					'encrypted' => 0
				);
			}
		}
		
		return $meta;	
	}
	
	/**
	 * Deletes the package on the remote server. Sets Input errors on failure,
	 * preventing the package from being deleted.
	 *
	 * /DONE/
	 */
	public function deletePackage($package) {
		// Nothing to do
		return null;
	}
	
	/**
	 * Returns all fields used when adding/editing a package, including any
	 * javascript to execute when the page is rendered with these fields.
	 *
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 *
	 * /DONE/ 
	 */
	public function getPackageFields($vars=null) {
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Fetch all packages available for the given server or server group
		$module_row = $this->getModuleRowByServer((isset($vars->module_row) ? $vars->module_row : 0), (isset($vars->module_group) ? $vars->module_group : ""));
			
		// $package->meta->templates = array();
		// Load Arrays
		$nodes = array();
		$templates = array();
			
		$nodes_res = $this->getNodes($module_row);
		
		foreach ($nodes_res AS  $node => $value) {
			$nodes[$value['node']] = $value['node'];
		}
	
		// Load more server info when the type is set
		if ($module_row && !empty($vars->meta['type']) ) {
			// Load templates
			$templates = $this->getTemplates(reset($nodes) , $vars->meta['type'] , $module_row);
		}
		
		// Remove nodes from 'available' if they are currently 'assigned'
		if (isset($vars->meta['nodes'])) {
			$this->assignGroups($nodes, $vars->meta['nodes']);
			
			// Set the node value as the node key
			$temp = array();
			foreach ($vars->meta['nodes'] as $key => $value)
				$temp[$value] = $value;
			$vars->meta['nodes'] = $temp;
			unset($temp, $key, $value);
		}
		
		// Remove nodes from 'available' if they are currently 'assigned'
		if (isset($vars->meta['templates'])) {
			$this->assignGroups($templates, $vars->meta['templates']);
			
			$temp = array();
			foreach ($vars->meta['templates'] as $key => $value)
				$temp[$value] = $value;
			$vars->meta['templates'] = $temp;
			unset($temp, $key, $value);
		}

		$fields = new ModuleFields();
		
		// Show nodes, and set javascript field toggles
		$this->Form->setOutput(true);
		$fields->setHtml("		
			<table class='table table_options'>
				<thead>
					<tr class='heading_row'>
						<td></td>
						<td>" . Language::_("Proxmoxv2.package_fields.reinstall", true) . "</td>						
						<td>" . Language::_("Proxmoxv2.package_fields.stats", true) . "</td>
						<td>" . Language::_("Proxmoxv2.package_fields.console", true) . "</td>
						<td>" . Language::_("Proxmoxv2.package_fields.tasks", true) . "</td>
						<td class='last'>" . Language::_("Proxmoxv2.package_fields.backup", true) . "</td>
					</tr>
				</thead>
				<tbody>
					<tr >
						<td >". Language::_("Proxmoxv2.package_fields.allow_client", true) . "</td>
						<td >". $this->Form->fieldCheckbox("meta[allow][reinstall]", "1", $this->Html->ifSet($vars->meta['allow']['reinstall']) == "1", array('id'=>"reinstall")) ."</td>						
						<td >". $this->Form->fieldCheckbox("meta[allow][stats]", "1", $this->Html->ifSet($vars->meta['allow']['stats']) == "1", array('id'=>"stats")) ."</td>
						<td >". $this->Form->fieldCheckbox("meta[allow][console]", "1", $this->Html->ifSet($vars->meta['allow']['console']) == "1", array('id'=>"console")) ."</td>
						<td >". $this->Form->fieldCheckbox("meta[allow][tasks]", "1", $this->Html->ifSet($vars->meta['allow']['tasks']) == "1", array('id'=>"tasks")) ."</td>
						<td class='last'>". $this->Form->fieldCheckbox("meta[allow][backup]", "1", $this->Html->ifSet($vars->meta['allow']['backup']) == "1", array('id'=>"backup")) ."</td>
					</tr>
				</tbody>
			</table>
		
			<table>
				<tr>
					<td>" . Language::_("Proxmoxv2.package_fields.assigned_nodes", true) . "</td>
					<td></td>
					<td>" . Language::_("Proxmoxv2.package_fields.available_nodes", true) . "</td>
				</tr>
				<tr>
					<td>
						" . $this->Form->fieldMultiSelect("meta[nodes][]", $this->Html->ifSet($vars->meta['nodes'], array()), array(), array("id"=>"assigned_nodes")) . "
					</td>
					<td><a href='#' class='move_left' id='move_left_nodes' >&nbsp;</a> &nbsp; <a href='#' class='move_right' id='move_right_nodes'>&nbsp;</a></td>					
					<td>
						" . $this->Form->fieldMultiSelect("available_nodes[]", $this->Html->ifSet($nodes, array()), array(), array("id"=>"available_nodes")) . "
					</td>
				</tr>
			</table>
			
			<table>
				<tr>
					<td>" . Language::_("Proxmoxv2.package_fields.assigned_templates", true) . "</td>
					<td></td>
					<td>" . Language::_("Proxmoxv2.package_fields.available_templates", true) . "</td>
				</tr>
				<tr>
					<td>
						" . $this->Form->fieldMultiSelect("meta[templates][]", $this->Html->ifSet($vars->meta['templates'], array()), array(), array("id"=>"assigned_templates")) . "
					</td>
					<td><a href=\"#\" class=\"move_left\" id=\"move_left_templates\" >&nbsp;</a> &nbsp; <a href=\"#\" class=\"move_right\" id=\"move_right_templates\">&nbsp;</a></td>
					<td>
						" . $this->Form->fieldMultiSelect("available_templates[]", $this->Html->ifSet($templates, array()), array(), array("id"=>"available_templates")) . "
					</td>
				</tr>
			</table>			
			
			<script type=\"text/javascript\">
				$(document).ready(function() {
					toggleProxmoxFields();
					
					$('#proxmox_type').change(function() {
						toggleProxmoxFields();
					});
					
					$('#proxmox_type').change(function() {
						fetchModuleOptions();
					});
					
					// Select all assigned nodes on submit
					$('#assigned_nodes').closest('form').submit(function() {
						selectAssignedNodes();
					});
					
					// Select all assigned templates on submit
					$('#assigned_templates').closest('form').submit(function() {
						selectAssignedTemplates();
					});					
				
					// Move nodes from right to left
					$('#move_left_nodes').click(function() {
						$('#available_nodes option:selected').appendTo($('#assigned_nodes'));
						return false;
					});
					// Move nodes from left to right
					$('#move_right_nodes').click(function() {
						$('#assigned_nodes option:selected').appendTo($('#available_nodes'));
						return false;
					});
					
					// Move templates from right to left
					$('#move_left_templates').click(function() {
						$('#available_templates option:selected').appendTo($('#assigned_templates'));
						return false;
					});
					// Move templates from left to right
					$('#move_right_templates').click(function() {
						$('#assigned_templates option:selected').appendTo($('#available_templates'));
						return false;
					});			
					
				});	
					
				function selectAssignedNodes() {
					$('#assigned_nodes option').attr('selected', 'selected');
				}
				
				function selectAssignedTemplates() {
					$('#assigned_templates option').attr('selected', 'selected');
				}
				
				function toggleProxmoxFields() {
					// Hide fields dependent on this value
					if ($('#proxmox_type').val() == '') {
						$('#assigned_templates').closest('table').hide();
					}
					// Show fields dependent on this value
					else {
						$('#available_templates').closest('table').show();
					}
				}				
			</script>
			<style>
			.module_option_fields .pad label{display:inline-block; width : 150px;}
			.module_option_fields .pad .table_options{width : auto !important;margin-bottom: 10px;}
			</style>
		");
		
		
		// Set the Proxmox type as a selectable option
		$storages = array('' => Language::_("Proxmoxv2.please_select", true)) + $this->getStorages($module_row);
		$storage = $fields->label(Language::_("Proxmoxv2.package_fields.storage", true), "proxmox_storage");
		$storage->attach($fields->fieldSelect("meta[storage]", $storages, $this->Html->ifSet($vars->meta['storage']), array('id' => "proxmox_storage")));
		$fields->setField($storage);
		unset($storage);
		
		// Set the Proxmox type as a selectable option
		$types = array('' => Language::_("Proxmoxv2.please_select", true)) + $this->getTypes();
		$type = $fields->label(Language::_("Proxmoxv2.package_fields.type", true), "proxmox_type");
		$type->attach($fields->fieldSelect("meta[type]", $types, $this->Html->ifSet($vars->meta['type']), array('id' => "proxmox_type")));
		$fields->setField($type);
		unset($type);
		
		// Select Pool ressource for the containers
		$pools = array('' => '') + $this->getPools($module_row);
		$pool = $fields->label(Language::_("Proxmoxv2.package_fields.pool", true), "proxmox_pool");
		$pool->attach($fields->fieldSelect("meta[pool]", $pools , $this->Html->ifSet($vars->meta['pool']), array('id' => "proxmox_pool")));
		$tooltip = $fields->tooltip(Language::_("Proxmoxv2.package_fields.tooltip.pool", true));
		$pool->attach($tooltip);
		$fields->setField($pool);
		unset($pool);
		
        // Set onboot 
		$onboot = $fields->label(Language::_("Proxmoxv2.package_fields.onboot", true), "proxmox_onboot" , array('class'=>"inline"));
		$onboot->attach($fields->fieldSelect("meta[onboot]", $this->getYesNo() , $this->Html->ifSet($vars->meta['onboot']), array('id' => "proxmox_onboot")));
		$tooltip = $fields->tooltip(Language::_("Proxmoxv2.package_fields.tooltip.onboot", true));
		$onboot->attach($tooltip);			
		$fields->setField($onboot);
		
		$commun_fields = array("cpus", "cpuunits", "disk", "memory");
		// Set Settings in openvz/qemu type
		foreach ($commun_fields as $value) {
			$field = $fields->label(Language::_("Proxmoxv2.package_fields.$value", true), "proxmox_$value" , array('class'=>"inline"));
			$field->attach($fields->fieldText("meta[$value]", $this->Html->ifSet($vars->meta[$value]) , array('id'=>"proxmox_$value", 'class'=>"inline")));			
			$tooltip_lang = Language::_("Proxmoxv2.package_fields.tooltip.$value" , true) ;
				if ($this->Html->ifSet($tooltip_lang)){
					$tooltip = $fields->tooltip(Language::_("Proxmoxv2.package_fields.tooltip.$value", true));
					$field->attach($tooltip);
				}
			$fields->setField($field);	
			unset($field);
		}
		
		$openvz_fields = array("swap", "quotatime", "quotaugidlimit");		
		// Set Settings in openvz type
		if ($vars->meta['type'] == "openvz") {		
			foreach ($openvz_fields as $value) {
				$field = $fields->label(Language::_("Proxmoxv2.package_fields.$value", true), "proxmox_$value" , array('class'=>"inline"));
				$field->attach($fields->fieldText("meta[$value]", $this->Html->ifSet($vars->meta[$value]) , array('id'=>"proxmox_$value", 'class'=>"inline")));
				$tooltip_lang = Language::_("Proxmoxv2.package_fields.tooltip.$value" , true) ;
					if ($this->Html->ifSet($tooltip_lang)){
						$tooltip = $fields->tooltip(Language::_("Proxmoxv2.package_fields.tooltip.$value", true));
						$field->attach($tooltip);
					}
				$fields->setField($field);
				unset($field);
			}
		}
		
		$qemu_fields = array("netspeed");		
		// Set Settings in qemu type
		if ($vars->meta['type'] == "qemu") {
			foreach ($qemu_fields as $value) {
				$field = $fields->label(Language::_("Proxmoxv2.package_fields.$value", true), "proxmox_$value" , array('class'=>"inline"));
				$field->attach($fields->fieldText("meta[$value]", ($this->Html->ifSet($vars->meta[$value]) ? $vars->meta[$value] : "0") , array('id'=>"proxmox_$value", 'class'=>"inline")));
				$tooltip_lang = Language::_("Proxmoxv2.package_fields.tooltip.$value" , true) ;
					if ($this->Html->ifSet($tooltip_lang)){
						$tooltip = $fields->tooltip(Language::_("Proxmoxv2.package_fields.tooltip.$value", true));
						$field->attach($tooltip);
					}
				$fields->setField($field);
				unset($field);
			}
		}

		return $fields;
	}	

	/**
	 * Attempts to validate service info. This is the top-level error checking method. Sets Input errors on failure.
	 *
	 * /DONE/
	 */
	public function validateService($package, array $vars=null, $edit=false) {
		// Set rules
		$rules = array(
			'hostname' => array(
				'format' => array(
					'rule' => array(array($this, "validateHostName")),
					'message' => Language::_("Proxmoxv2.!error.Proxmoxv2_hostname.format", true)
				)
			),
			'ostemplate' => array(
				'empty' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("Proxmoxv2.!error.Proxmoxv2_template.valid", true)
				)
			),				
		);
		
		// Set fields to optional
		if ($edit) {
			$rules['hostname']['format']['if_set'] = true;
			unset($rules['ostemplate']);
		}
		
		$this->Input->setRules($rules);
		return $this->Input->validates($vars);
	}
	
	/**
	 * Returns an array of key values for fields stored for a module, package,
	 * and service under this module, used to substitute those keys with their
	 * actual module, package, or service meta values in related emails.
	 *
	 * /DONE/ NOT FUlly
	 */
	public function getEmailTags() {
		return array(
			'module'	=> array("hostname", "port"),
			'package'	=> array("name"),
			'service'	=> array("vmid" , "options.hostname" , "options.ip_address" , "options.ostemplate" , "options.password"
				/* , "proxmox_node", "proxmox_vmid", "proxmox_username", "proxmox_memory", "proxmox_hdd", "proxmox_cpu",
				"proxmox_type", "proxmox_netspeed"*/
			)
		);
	}	
	
	
//******************************//
//** PRIVATE FUNCTION SECTION **//
//******************************//
	
	/**
	 * Retrieves the module row given the server or server group
	 *
	 * /DONE/
	 */
	private function getModuleRowByServer($module_row, $module_group = "") {
		// Fetch the module row available for this package
		$row = null;
		if ($module_group == "") {
			if ($module_row > 0) {
				$row = $this->getModuleRow($module_row);
			}
			else {
				$rows = $this->getModuleRows();
				if (isset($rows[0]))
					$row = $rows[0];
				unset($rows);
			}
		}
		else {
			// Fetch the 1st server from the list of servers in the selected group
			$rows = $this->getModuleRows($module_group);
			
			if (isset($rows[0]))
				$row = $rows[0];
			unset($rows);
		}
		
		return $row;
	}
	
	/**
	 * Sets the assigned and available groups. Manipulates the $available_groups by reference.
	 *
	 * /DONE/
	 */
	private function assignGroups(&$available_groups, $assigned_groups) {
		// Remove available groups if they are assigned
		foreach ($assigned_groups as $key => $value) {
			if (isset($available_groups[$value]))
				unset($available_groups[$value]);
		}
	}
	
	/**
	 * Return list of Yes/No
	 *
	 * /DONE/
	 */
	private function getYesNo() {
		return array(
			true	=> Language::_("Proxmoxv2.select.yes", true),
			false 	=> Language::_("Proxmoxv2.select.no", true)
		);
	}	
	
	/**
	 * Retrieves a list of server types and their language
	 *
	 * /DONE/
	 */
	private function getTypes() {
		return array(
			'openvz'	=> Language::_("Proxmoxv2.types.openvz", true),
			'qemu'		=> Language::_("Proxmoxv2.types.kvm", true)
		);
	}
	
	/**
	 * a list of OS type Supported for KVM
	 * ostype: (l24 | l26 | other | solaris | w2k | w2k3 | w2k8 | win7 | win8 | wvista | wxp)
	 * 
	 * /DONE/
	 */
	private function getOsTypes() {
		return array(
			'win8'		=> "Microsoft Windows 8/2012",
			'win7'		=> "Microsoft Windows 7",
			'wvista'	=> "Microsoft Windows Vista",
			'w2k8'		=> "Microsoft Windows 2008",
			'w2k3'		=> "Microsoft Windows 2003",
			'w2k'		=> "Microsoft Windows 2000",
			'wxp'		=> "Microsoft Windows XP" ,
			'l24'		=> "Linux 2.4 Kernel" ,
			'l26'		=> "Linux 2.6/3.X Kernel" ,
			'solaris'	=> "solaris/opensolaris/openindiania kernel" ,
			'other'		=> "unspecified OS" 
		);
	}		

	/**
	 * Fetches the nodes available for the Proxmox server
	 *
	 * /DONE/
	 */
	private function getNodes($module_row) {

		$response	= null;
		$command	= "get" ;	
		$path		= "nodes" ;
			
		
		try {
			$response = $this->SendCommand($module_row , $command , $path );
		}
		catch (Exception $e) {
			// Nothing to do
			return array();
		}
		
		// Return the nodes
		if ($response) 
			return $response;
		
		return array();
	}

	/**
	 * Fetches the stogares available for the Proxmox server 
	 *
	 * /DONE/
	 */
	private function getStorages($module_row) {

		$response	= null;
		$command	= "get" ;
		$path		= "storage" ;
		
		try {
			$response = $this->SendCommand($module_row , $command , $path );
		}
		catch (Exception $e) {
			// Nothing to do
			return array();
		}
		
		// Return the nodes
		if ($response) {
		
			foreach ($response AS $storage) {
				$storages[$storage['storage']] = $storage['storage'] ;
			}		

			return $storages;
		}
		return array();
	}

	/**
	 * Retrieves a list of the virtual server templates for the given types
	 *
	 * /DONE/
	 */
	private function getTemplates($node , $type , $module_row) {
	
		$response	= null;
		$command	= "get" ;
		$path		= "nodes/" .  $node . "/storage/local/content" ;
		$params = array() ;
		
		if ($type =="openvz")
			$params['content'] = "vztmpl" ;
		else if ($type =="qemu")
			$params['content'] = "iso" ;	
		
		try {
			$response = $this->SendCommand($module_row , $command , $path , $params );
		}
		catch (Exception $e) {
			// Nothing to do
			return array();
		}
		
		// Return the templates
		if ($response) {
		
			$result = array();
			
			foreach($response as $file) {
				
				$search    = array("local:", "vztmpl/" , "iso/" , ".tar.gz", ".iso");
				$replace   = array("", "", "", "", "");
				$template  = str_replace($search, $replace, $file['volid']);
				
				$result[$template] = $template;
			
			}
			
			return $result;			
			
		}
		return array();
	}	
	
	/**
	 * Fetches the bool available for the Proxmox server 
	 *
	 * /DONE/
	 */
	private function getPools($module_row) {

		$response	= null;
		$command	= "get" ;
		$path		= "pools" ;
			
		
		try {
			$response = $this->SendCommand($module_row , $command , $path );
		}
		catch (Exception $e) {
			// Nothing to do
			return array();
		}
		
		// Return the nodes
		if ($response) {
		
			foreach ($response AS $pool) {
				$pools[$pool['poolid']] = $pool['comment'] ;
			}

			return $pools;
		}
		return array();
	}	

	/**
	 * Returns an array of service fields to set for the service using the given input
	 *
	 * /DONE/ 
	 */
	private function getFieldsFromInput(array $vars, $package) {
	
		// Determine which node to assign the service to
		$module_row = $this->getModuleRow($package->module_row);
		
		$node = $this->chooseNode($package->meta->nodes, $module_row);
		$vmid = (isset($vars['vmid']) ? $vars['vmid'] : $this->NextID($module_row)) ;
		$available_ips = explode("\n", $module_row->meta->ips) ;
		
		$fields = array(
			'type'			=> $package->meta->type,
			'onboot'		=> $package->meta->onboot,
			'node'			=> $node,		
			'vmid'			=> $vmid,
			'storage'		=> $package->meta->storage ,
			//
			'hostname'		=> isset($vars['hostname']) ? $vars['hostname'] : null,
			'password'		=> $this->generatePassword(), // root password
			'memory'		=> $package->meta->memory , 
			'cpuunits'		=> $package->meta->cpuunits
		);
		
		if ($package->meta->pool)
			$fields['pool']				= $package->meta->pool ;
		
		// Set Field For Openvz VM
		if ($package->meta->type == "openvz") {
			$fields['ostemplate']		= "local:vztmpl/". $vars['ostemplate'] .".tar.gz" ;
			$fields['disk']				= $package->meta->disk ;
			$fields['cpus']				= $package->meta->cpus ;
			$fields['swap']				= $package->meta->swap ;
			$fields['quotatime']		= $package->meta->quotatime ;
			$fields['quotaugidlimit']	= $package->meta->quotaugidlimit ;
			$fields['ip_address']		= trim(array_shift($available_ips));
		}		
		
		// Set Field For KVM VM
		if ($package->meta->type == "qemu") {		
			$fields['sockets']	= $package->meta->cpus ;
			$fields['net0']		= "virtio,bridge=vmbr0,rate=" .  $package->meta->netspeed ;
			$fields['virtio0']	= $package->meta->storage . ":" . $package->meta->disk;		
		}			
		
		return $fields;
	}	

	/**
	 * Get the status of a given task
	 *
	 * /DONE/
	 */
	private function Task($module_row, $node , $taskid) {

		$response	= null;
		$command	= "get" ;
		$path		= "nodes/" . $node . "/tasks/". $taskid ."/status" ;
			
		$response = $this->SendCommand($module_row , $command , $path );
		
		// Return the nodes
		if ($response) 
			return $response;
		else 
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.task", true , $taskid))));
			
		if ($this->Input->errors())
			return;	
	}

	/**
	 * Get alist fo task for a given VM
	 *
	 * /DONE/
	 */
	private function Tasks($module_row, $node , $vmid=null , $count ,  $get=null , $client=false) {

		$response	= null;
		$params		= array() ;	
		$command	= "get" ;
		$path		= "nodes/" . $node . "/tasks/" ;
		
		$get_key = "3";
		if ($client)
			$get_key = "2";
			
		if (isset($vmid))
			$params['vmid'] = $vmid;
			
		if (!$count)
			$params['limit'] = 20 ;
		else 
			unset($params['limit']);
			
		// Perform actions
		if (array_key_exists($get_key, (array)$get)) {			
			$params['start'] = (max(1,  $get[$get_key]) - 1)*20 ;
		}
			

		
		$response = $this->SendCommand($module_row , $command , $path , $params );
		
		// Return the nodes
		if ($response) 
			return $response;
		else 
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.tasks", true , $vmid))));
			
		if ($this->Input->errors())
			return;	
	}		

	/**
	 * Fetches the next VM ID available in the cluster
	 *
	 * /DONE/
	 */
	private function NextID($module_row) {

		$response	= null;
		$command	= "get" ;
		$path		= "cluster/nextid" ;
			
		
		try {
			$response = $this->SendCommand($module_row , $command , $path );
		}
		catch (Exception $e) {
			// Nothing to do
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal", true))));
			
		}
		
		// Return the nodes
		if ($response) 
			return $response;
		
		if ($this->Input->errors())
			return;	
	}	

	/**
	 * Actions To VM Machine (start/shutdown/stop/mount/unmount/reinstall/current/ubc)
	 *
	 * /DONE/
	 */
	 /*
	private function ActionVMPost($module_row , $commands , $node , $type , $vmid , $action , array $params) {

		$response	= null;
		$command	=  $commands ;
		$path		= "nodes/". $node ."/". $type ."/". $vmid ."/status/". $action ;
			
		$response = $this->SendCommand($module_row , $command , $path , $params);		

		if ($response)
			return $response;
		else 	
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $action))));
			
		if ($this->Input->errors())
			return;	
	}
	 */

	/**
	 * Create VM
	 *
	 * /DONE/
	 */
	private function CreateVM($module_row, array $params) {
		
		$node	= $params['node'] ;
		$type	= $params['type'] ;
		unset($params['node'] , $params['type'] );
		
		// $success	= false;
		$response	= null;
		$command	= "post" ;
		$path		= "nodes/" .  $node . "/" .  $type ;
		
		
		switch($type) {
			case "qemu":
				// Create The virtual Machine
				
				break;
			case "openvz":
				// Create The virtual Machine
				$response = $this->SendCommand($module_row , $command , $path , $params);
				break;
		}
		
		if (!$response)
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.create", true , $node))));
		
		if ($this->Input->errors())
			return;
			
		$finished = false; 
		
		while( !$finished  ) {
			$task = $this->Task($module_row ,  $node  , $response) ;
			
			if ($task['status'] == "running")
				$finished = false;
			else
				$finished = true;
				
			sleep(2);
		}
		
		$path = "nodes/" . $node . "/" . $type . "/" . $params['vmid'] . "/status/start";				
		$result = $this->SendCommand($module_row , $command , $path );
		
		if(empty($result)){
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, "start" ))));
		}					
		
		if ($this->Input->errors())
			return;
			
		return true;
	}

	/**
	 * Delete/Destroy VM
	 *
	 * /DONE/
	 */
	private function DeleteVM($module_row, $service_fields ) {
		// stop the vps
		$result	= null;
		
		$vps =  $this->SendCommand($module_row ,"get" , "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/current" );
		if ($vps['status'] == "running") {
			$command	= "post" ;				
			$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/stop";
			
			$result = $this->SendCommand($module_row , $command , $path );
			
			if(empty($result)){
				$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, "Stop" ))));
			}
			// be sure that the vps is stopped
			$finished = false; 					
			while( !$finished  ) {
				$task = $this->Task($module_row ,  $service_fields->options['node']  , $result) ;						
				if ($task['status'] == "running")
					$finished = false;
				else
					$finished = true;							
				sleep(1);
			}
		}
		
		// Delete the vps again					
		$response	= null;
		$command	= "delete" ;
		$path		= "nodes/" .  $service_fields->options['node'] . "/" .  $service_fields->options['type'] ."/" . $service_fields->vmid ;
		$response = $this->SendCommand($module_row , $command , $path );
		
		if ($response) 
			return $response;
		else 
			$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.delete", true , $service_fields->vmid ))));

		if ($this->Input->errors())
			return;		
	}
	
	/**
	 * Chooses the best node to assign a service onto based on the resources of available nodes
	 *
	 * /DONE/
	 */
	private function chooseNode($nodes, $module_row) {
		$node = "";
		
		if (count($nodes) == 1)
			$node = $nodes[0];
		else {
			$best_node = array(
				'name' => "",
				'value' => 0
			);
			
			// 1 MB in bytes
			$megabyte = 1048576;
			
			// Determine the best node
			foreach ($nodes as $node_id) {
				// Fetch node stats
				$node_stats = $this->getNodeStatistics($node_id, $module_row);
				
				// Use disk/memory to compare which node has the most available resources
				$disk = (float)$node_stats->data->rootfs->free;
				$memory = (float)$node_stats->data->memory->free;
				$total_value = $disk + $memory;
				
				// If any one of the resources is too low, skip this node when we have another
				if ($best_node['value'] != 0 && ($disk <= $megabyte || $memory <= $megabyte))
					continue;
				
				// Set the best node to the one with the largest combined free resources
				if ($total_value > $best_node['value'])
					$best_node = array('name' => $node_id, 'value' => $total_value);
			}
			
			$node = $best_node['name'];
		}
		
		return $node;
	}

	/**
	 * Retrieves a list of node statistics, e.g. freememory, freedisk, etc.
	 *
	 * /DONE/
	 */
	private function getNodeStatistics($node_id, $module_row) {
		
		$response	= null;
		$command	= "get" ;
		$path		= "nodes/" . $node_id . "/status";
		
		try {
			$response = $this->SendCommand($module_row , $command , $path);
		}
		catch (Exception $e) {
			// Nothing to do
			return array();
		}
		
		// Return the templates
		if ($response) {			
			return $response;			
		}
		
		return new stdClass();
	}

//*******************************//
//****** SERVICES ACTIONS ******//
//******************************//
	
	/**
	 * Adds the service to the remote server. Sets Input errors on failure,
	 * preventing the service from being added.
	 *
	 * /DONE/
	 */
	public function addService($package, array $vars=null, $parent_package=null, $parent_service=null, $status="pending") {
		// Load the API
		$module_row = $this->getModuleRow();
		
		// Get the fields for the service
		$params = $this->getFieldsFromInput($vars, $package);				

		// Validate the service-specific fields
		$this->validateService($package, $vars);
		
		if ($this->Input->errors())
			return;

		// Only provision the service if 'use_module' is true
		if ($vars['use_module'] == "true") {
		
			try {
				$client_id = (isset($vars['client_id']) ? $vars['client_id'] : "");
				$params['description'] = Language::_("Proxmoxv2.activated.client_id", true , $client_id );
				
				$this->CreateVM($module_row , $params);
				
				// $response = $this->CreateVM($module_row , $params);				
				// if(!$response)
					// $this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal", true))));				
				// if ($this->Input->errors())
					// return;
								
				// $this->ActionVMPost($module_row , "post" , $params['node'] , $params['type'], $params['vmid'], "start" , array() ) ;
	
			}
			catch (Exception $e) {
				$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal", true))));
			}

			if ($this->Input->errors())
				return;

		}
		
		// Return service fields
		return array(
			array(
				'key' => "vmid",
				'value' => $params['vmid'],
				'encrypted' => 0
			),
			array(
				'key' => "options",
				'value' => 	array(
								'type' => $params['type'] ,
								'node' => $params['node'] ,
								'hostname' => $params['hostname'],
								'password' => $params['password'],
								'ostemplate' =>(isset($params['ostemplate']) ? $params['ostemplate'] : null) ,
								'ip_address' => (isset($params['ip_address']) ? $params['ip_address'] : null) 
							),
				'encrypted' => 0
			)
		);
	}	

	/**
	 * Edit the service to the remote server. Sets Input errors on failure,
	 * preventing the service from being edited.
	 *
	 * /DONE/
	 */
	public function editService($package, $service, array $vars=null, $parent_package=null, $parent_service=null) {
		// Load the API
		$module_row = $this->getModuleRow();		
		
		// Validate the service-specific fields
		$this->validateService($package, $vars, true);
		
		if ($this->Input->errors())
			return;
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Only provision the service if 'use_module' is true
		if ($vars['use_module'] == "true") {
			// Nothing to do
		}
		
		// Return all the service fields
		$service_fields->vmid =  $vars['vmid'] ;
		$fields = array();
		$encrypted_fields = array();
		foreach ($service_fields as $key => $value)
			$fields[] = array('key' => $key, 'value' => $value, 'encrypted' => (in_array($key, $encrypted_fields) ? 1 : 0));
		
		return $fields;
	}	

	/**
	 * Cancel the service to the remote server. Sets Input errors on failure,
	 * preventing the service from being canceled.
	 *
	 * /DONE/
	 */
	public function cancelService($package, $service, $parent_package=null, $parent_service=null) {
		
		if (($row = $this->getModuleRow())) {
			$module_row = $this->getModuleRow();
			
			$service_fields = $this->serviceFieldsToObject($service->fields);
			
			$this->DeleteVM($module_row , $service_fields);

		}
		return null;
	}
	
//******************************//
//***** ADMIN SIDE SECTION *****//
//******************************//

	/**
	 * Returns all fields to display to an admin attempting to add a service with the module
	 *
	 * /DONE/
	 */
	public function getAdminAddFields($package, $vars=null) {
	
		Loader::loadHelpers($this, array("Html"));

		// Fetch the module row available for this package
		$module_row = $this->getModuleRowByServer((isset($package->module_row) ? $package->module_row : 0), (isset($package->module_group) ? $package->module_group : ""));

		$nextvmid = $this->NextID($module_row);

		$fields = new ModuleFields();		
		
		// print_r($package);
		print_r($vars);
		
		// Load nodes
		$assigned_nodes = $package->meta->nodes;		
		foreach ($assigned_nodes AS $node) {			
			$osnodes[$node] = ucwords($node);
		}

		// Set the nodes as a selectable option
		$nodes = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_node", true), "nodes");
		$nodes->attach($fields->fieldSelect("nodes",  $osnodes , $this->Html->ifSet($vars->nodes), array('id' => "nodes")));
		$fields->setField($nodes);

		// Create hostname label
		$hostname = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_hostname", true), "hostname");
		$hostname->attach($fields->fieldText("hostname", ($this->Html->ifSet($vars->hostname) ? $this->Html->ifSet($vars->hostname) : $this->Html->ifSet($vars->options['hostname']) ), array('id'=>"hostname", 'placeholder'=>"server.yourdomain.com" )));
		$fields->setField($hostname);

		// Load templates
		$assigned_templates = $package->meta->templates;
		foreach ($assigned_templates AS $template) {
			$search_name   = array("-1_", "-2_", "-", "i386"     , "amd64"    , "turnkey");
			$replace_name  = array( " " , " "  , " ", "(32 Bits)", "(64 Bits)", "");				
			$template_name = str_replace($search_name, $replace_name, $template);			
			$templates[$template] = ucwords($template_name);
		}

		// Set the Templates as a selectable option
		$ostemplate = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_template", true), "ostemplate");
		$ostemplate->attach($fields->fieldSelect("ostemplate",  $templates , $this->Html->ifSet($vars->options['ostemplate']), array('id' => "ostemplate")));
		$fields->setField($ostemplate);

		// Create vmid label		
		$vmid = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_vmid", true) . Language::_("Proxmoxv2.!tooltip.vmid", true) , "vmid");
		$vmid->attach($fields->fieldText("vmid", $nextvmid , array('id'=>"vmid")));	
		$fields->setField($vmid);

		return $fields;
	}

	/**
	 * Returns all fields to display to an admin attempting to edit a service with the module
	 *
	 * /DONE/
	 */	
	public function getAdminEditFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html","Form" ));
		
		// Fetch the module row available for this package
		$module_row = $this->getModuleRowByServer((isset($package->module_row) ? $package->module_row : 0), (isset($package->module_group) ? $package->module_group : ""));

		$fields = new ModuleFields();
	
		// Create hostname label
		// $hostname = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_hostname", true), "hostname");
		// $hostname->attach($fields->fieldText("hostname", $this->Html->ifSet($vars->options['hostname']) , array('id'=>"hostname", 'placeholder'=>"server.yourdomain.com" )));
		// $fields->setField($hostname);

		// Create vmid label
		$vmid = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_vmid", true) . Language::_("Proxmoxv2.!tooltip.vmid", true) , "vmid");
		$vmid->attach($fields->fieldText("vmid", $this->Html->ifSet($vars->vmid) , array('id'=>"vmid")));	
		$fields->setField($vmid);	

		// Edit vmid in local mode, and set javascript field toggles
		$this->Form->setOutput(true);
		$fields->setHtml("
			<script type=\"text/javascript\">
				$(document).ready(function() {
				/*
					$('input[name=\"vmid\"]').prop('disabled', true);

					//show it when the checkbox is clicked
					$('input[id=\"use_module_module\"]').on('click', function () {
						if ($(this).prop('checked')) {
							$('input[name=\"vmid\"]').prop('disabled', true);							
						} else {
							$('input[name=\"vmid\"]').prop('disabled', false);
						}
					});	
				*/
				});				
			</script>
		");
		
		return $fields;
	}	

	/**
	 * Returns all tabs to display to an admin when managing a service whose
	 * package uses this module
	 *
	 * /DONE/
	 */
	public function getAdminTabs($package) {
		$tabs = array(
			'tabActions'	=> Language::_("Proxmoxv2.tab_actions", true),
			'tabConfig'		=> Language::_("Proxmoxv2.tab_config", true),
			'tabNetwork'	=> Language::_("Proxmoxv2.tab_network", true),
			'tabStats'		=> Language::_("Proxmoxv2.tab_stats", true),
			'tabTasks'		=> Language::_("Proxmoxv2.tab_tasks", true),
			'tabConsole'	=> Language::_("Proxmoxv2.tab_console", true),
			'tabReinstall'	=> Language::_("Proxmoxv2.tab_reinstall", true),
			
			// 'tabBackup'		=> Language::_("Proxmoxv2.tab_backup", true),			
			// 'tabFirewall'	=> Language::_("Proxmoxv2.tab_firewall", true),
			// 'tabSnapshoot'	=> Language::_("Proxmoxv2.tab_snapshoot", true),
		);
		
		return $tabs ;
	}

//******************************//
//***** CLIENT SIDE SECTION ******//
//******************************//	

	/**
	 * Returns all fields to display to a client attempting to add a service with the module
	 *
	 * /DONE/
	 */	
	public function getClientAddFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html"));
		
		// Fetch the module row available for this package
		$module_row = $this->getModuleRowByServer((isset($package->module_row) ? $package->module_row : 0), (isset($package->module_group) ? $package->module_group : ""));

		$fields = new ModuleFields();
		
		// Create hostname label
		$hostname = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_hostname", true), "hostname");
		$hostname->attach($fields->fieldText("hostname", $this->Html->ifSet($vars->hostname) , array('id'=>"hostname", 'placeholder'=>"server.yourdomain.com" )));
		$fields->setField($hostname);
		
		// Load templates
		$templatesres = $package->meta->templates;
		foreach ($templatesres AS $template) {
			$search_name   = array("-1_", "-2_", "-", "i386"     , "amd64"    , "turnkey");
			$replace_name  = array( " " , " "  , " ", "(32 Bits)", "(64 Bits)", "");				
			$template_name = str_replace($search_name, $replace_name, $template);			
			$templates[$template] = ucwords($template_name);
		}
			
		// Set the Templates as a selectable option
		$ostemplate = $fields->label(Language::_("Proxmoxv2.service_field.Proxmoxv2_template", true), "ostemplate");
		$ostemplate->attach($fields->fieldSelect("ostemplate",  $templates , $this->Html->ifSet($vars->ostemplate), array('id' => "ostemplate")));
		$fields->setField($ostemplate);
	
		return $fields;
	}	

	/**
	 * Returns all tabs to display to an client when managing a service whose
	 * package uses this module
	 *
	 * /DONE/
	 */
	public function getClientTabs($package) {
		$tabs = array(
			'tabClientActions'	=> Language::_("Proxmoxv2.tab_actions", true),
		
			// 'tabClientConsole'	=> Language::_("Proxmoxv2.tab_console", true),
			// 'tabClientConfig'		=> Language::_("Proxmoxv2.tab_config", true),
			// 'tabClientNetwork'	=> Language::_("Proxmoxv2.tab_network", true),			
			// 'tabClientReinstall'	=> Language::_("Proxmoxv2.tab_reinstall", true),	
			// 'tabClientBackup'		=> Language::_("Proxmoxv2.tab_backup", true),			
			// 'tabClientFirewall'	=> Language::_("Proxmoxv2.tab_firewall", true),
			// 'tabClientSnapshoot'	=> Language::_("Proxmoxv2.tab_snapshoot", true),
		);
		// if ($package->meta->allow['stats'])
		if (isset($package->meta->allow['stats']))
			$tabs['tabClientStats']	= Language::_("Proxmoxv2.tab_stats", true);
			
		if (isset($package->meta->allow['console']))
			$tabs['tabClientConsole']	= Language::_("Proxmoxv2.tab_console", true);

		if (isset($package->meta->allow['tasks']))
			$tabs['tabClientTasks']	= Language::_("Proxmoxv2.tab_tasks", true);
			
		// next version	
		// if ($package->meta->allow['reinstall'])
			// $tabs['tabClientReinstall']	= Language::_("Proxmoxv2.tab_reinstall", true);
		
		// if ($package->meta->allow['backup'])
			// $tabs['tabClientBackup']	= Language::_("Proxmoxv2.tab_backup", true);
			
		return $tabs ;
	}

//******************************//
//****** TABS FOR ADMINS *******//
//******************************//	

	// Handle the Graph Stats TAB
	private function statsTabGraph($package, $service, $client=false, array $get=null, array $post=null) {
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$get_key = "3";
		if ($client)
			$get_key = "2";
		
		$result = null ;
		$images = array();
		$params = array() ;			
		$command = "get" ;
		$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/rrd";
			
		$params['timeframe'] = "day";
		$params['cf'] = "AVERAGE";
		
		if (!empty($post)) {
			$params['timeframe'] = $post['timeframe'];			
		}
		
		// Perform actions
		$graph_keys = array("mem,maxmem", "cpu", "netin,netout", "diskread,diskwrite");
		foreach ($graph_keys as $value) {
			$params['ds'] = $value  ;
			$result = $this->SendCommand($module_row , $command , $path , $params );					
			if(empty($result)){
				$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $value ))));
			}
			$images[$value] = base64_encode(utf8_decode($result['image']));

		}
		
		// if (array_key_exists($get_key, (array)$get)) {		
			// $params['ds'] = $get[$get_key] ;
			// header('Content-Type: image/png');
			// $image = base64_encode(utf8_decode($result->data->image));
			// echo base64_encode(utf8_decode($result['image']));
			// die;
		// }		

		return $images ;
	
	}

	// Handle the Action TAB
	private function actionsTab($package, $service, $client=false, array $get=null, array $post=null) {
			
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$get_key = "3";
		if ($client)
			$get_key = "2";
		
		$result = null ;
		$params = array() ;			
		$command = "post" ;
		
		if (!empty($post)) {
			$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/" . $post['action'] ;
			
			switch ($post['action']) {
				case "start":				
					$result = $this->SendCommand($module_row , $command , $path , $params );					
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}
					break;
					
				case "stop":
					$result = $this->SendCommand($module_row , $command , $path , $params );					
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}				
					break;
					
				case "shutdown":				
					$result = $this->SendCommand($module_row , $command , $path , $params );					
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}					
					break;
					
				case "reboot":
					// stop the vps
					$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/stop";
					$result = $this->SendCommand($module_row , $command , $path , $params );
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}
					// be sure that the vps is stopped
					$finished = false; 					
					while( !$finished  ) {
						$task = $this->Task($module_row ,  $service_fields->options['node']  , $result) ;						
						if ($task['status'] == "running")
							$finished = false;
						else
							$finished = true;							
						sleep(1);
					}
					// start the vps again
					$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/start";
					$result = $this->SendCommand($module_row , $command , $path , $params );
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}					
					break;
					
				case "umount":
					$result = $this->SendCommand($module_row , $command , $path , $params );					
					if(empty($result)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}
					break;	
				case "hostname":
					$params['hostname'] = $post['hostname'] ;
					$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/config";
					$response = $this->SendCommand($module_row , "put" , $path , $params);
					if(empty($response)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					}
					break;
				// case "settings":
					// unset($post['action']) ;
					// $params = $post ;
					// $path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/config";
					// $response = $this->SendCommand($module_row , "put" , $path , $params);
					// if(empty($response)){
						// $this->Input->setErrors(array('api' => array('internal' => Language::_("Proxmoxv2.!error.api.internal.action", true, $post['action'] ))));
					// }					
					// break;
			}		
		
		}
		
		// Get the vps Status
		$vps =  $this->SendCommand($module_row ,"get" , "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/status/current" );
		
		return $vps ;
	}

	/**
	 * Actions tab (boot, shutdown, etc.)
	 *
	 * /DONE/
	 */
	public function tabActions($package, $service, array $get=null, array $post=null , $client=false) {
	
		$template = ($client ? "tab_client_actions" : "tab_actions");
		$this->view = new View($template, "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$vps = $this->actionsTab($package, $service, false, $get, $post);		
				
		switch ($vps['status']) {
			case "running":
				$vps['running'] = true;
				break;
			case "stopped":
				$vps['stopped'] = true;
				break;			
			case "mounted":
				$vps['stopped'] = true;
				break;					
			default:
				break;
		}
		if (!empty($post)) {
			//
		}
		
		$vps['uptime'] = $this->ConvertSecondsToTime($vps['uptime']) ;
		
		// Fetch the server status
		$this->view->set("server", $vps );
		// Extra settings
		$this->view->set("type", $service_fields->options['type']);
		$this->view->set("yesno", $this->getYesNo() );
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
	}

	/**
	 * Config tab (settings.)
	 *
	 * /DONE/
	 */
	public function tabConfig($package, $service, array $get=null, array $post=null) {
	
		$this->view = new View("tab_config", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		$params = array() ;
		
		$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/config";

		if (!empty($post)) {
			$params = $post ;
			$this->SendCommand($module_row , "put" , $path , $params);	
		}
		
		$settings = $this->SendCommand($module_row , "get" , $path , $params);	
		
		$exclude_feilds = array('nameserver' , 'searchdomain' , 'ip_address' , 'digest' , 'hostname' , 'storage' , 'ostemplate' );
		foreach ( $exclude_feilds as $exclude ) {
			unset($settings[$exclude]);
		}
		
		// Fetch the server status
		$this->view->set("settings", $settings );
		// Extra settings
		$this->view->set("type", $service_fields->options['type']);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
	}

	/**
	 * Config tab (settings.)
	 *
	 * /DONE/
	 */
	public function tabNetwork($package, $service, array $get=null, array $post=null) {
	
		$this->view = new View("tab_network", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		$params = array() ;
		
		$path = "nodes/" . $service_fields->options['node'] . "/" . $service_fields->options['type'] . "/" . $service_fields->vmid . "/config";

		if (!empty($post)) {
			$params = $post ;
			$this->SendCommand($module_row , "put" , $path , $params);	
		}
		
		$settings = $this->SendCommand($module_row , "get" , $path , $params);	

		$exclude_feilds = array('hostname' , 'disk' , 'digest' , 'storage' , 'ostemplate' , 'onboot', 'swap' ,'description' ,'quotaugidlimit' , 'cpus', 'cpuunits', 'quotatime', 'memory' );
		foreach ( $exclude_feilds as $exclude ) {
			unset($settings[$exclude]);
		}
		
		// Fetch the server status
		$this->view->set("settings", $settings );
		// Extra settings
		$this->view->set("type", $service_fields->options['type']);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
	}
	
	/**
	 * Tab Stats
	 *
	 * /DONE/
	 */
	public function tabStats($package, $service, array $get=null, array $post=null, $client=false) {
	
		$template = ($client ? "tab_client_stats" : "tab_stats");
		$this->view = new View($template, "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);

		$images = $this->statsTabGraph($package, $service, $client, $get, $post);
		
		if (!empty($post)) {
			//TODO
		}

		$this->view->set("images", $images);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch() ;
	}	

	/**
	 * Tab Tasks
	 *
	 * /DONE/
	 */
	public function tabTasks($package, $service, array $get=null, array $post=null , $client=false) {
	
		$template = ($client ? "tab_client_tasks" : "tab_tasks");
		$this->view = new View($template, "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Date", "WidgetClient"));

		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);

		$tasks		= $this->Tasks($module_row , $service_fields->options['node'] , $service_fields->vmid , $count=null , $get , $client );
		sleep(1);
		$tasksall	= $this->Tasks($module_row , $service_fields->options['node'] , $service_fields->vmid , $count=true );
		
		$client_pagination = $this->base_uri . "services/manage/" . $this->Html->ifSet($service->id) . "/tabClientTasks/[p]/" ;
		$admin_pagination  = $this->base_uri . "clients/servicetab/" . $this->Html->ifSet($service->client_id) . "/" . $this->Html->ifSet($service->id) . "/tabTasks/[p]/" ;
		
		$pagin_admin = Configure::get("Blesta.pagination") ;
		$pagin_client = Configure::get("Blesta.pagination_client") ;
		
		$settings = array_merge(($client ? $pagin_client : $pagin_admin), array(
				'total_results' => count($tasksall),
				'uri'=> ($client ? $client_pagination : $admin_pagination),
				'params'=>array()
			)
		);
		
		Loader::loadHelpers($this, array("Pagination"=>array($get, $settings)));
		// $this->Pagination->setSettings(Configure::get("Blesta.pagination_ajax"));
		($client ? "" : $this->Pagination->setSettings(Configure::get("Blesta.pagination_ajax"))) ; 
		
		$this->view->set("tasks", $tasks);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
	}		

	/**
	 * Tab Reinstall
	 *
	 * /DONE/
	 */
	public function tabReinstall($package, $service, array $get=null, array $post=null) {
	
		$this->view = new View("tab_reinstall", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
	
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
	}		

	/**
	 * Tab Console
	 *
	 * /DONE/
	 */
	public function tabConsole($package, $service, array $get=null, array $post=null) {
	
		$this->view = new View("tab_console", "default");
		$this->view->base_uri = $this->base_uri;
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));

		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "proxmoxv2" . DS);
		return  $this->view->fetch();
		
	}	

//******************************//
//****** TABS FOR CLIENTS ******//
//******************************//	
	public function tabClientActions($package, $service, array $get=null, array $post=null) {
		return $this->tabActions($package, $service, $get,  $post , true );
		// return $view->fetch();
	}
	
	public function tabClientStats($package, $service, array $get=null, array $post=null) {
		return $this->tabStats($package, $service, $get,  $post , true );
		// return $view->fetch();
	}
	
	public function tabClientTasks($package, $service, array $get=null, array $post=null) {
		return $this->tabTasks($package, $service, $get,  $post , true );
	}
	
	public function tabClientConsole($package, $service, array $get=null, array $post=null) {
		return $this->tabConsole($package, $service, $get,  $post , true );
	}	
	
	
//******************************//
//*** PRIVATE FUNCTION SECTION ***//
//******************************//
	
	/**
	 * Convert a given Second to  readable human format
	 *
	 * /DONE/ 
	 */
	private function ConvertSecondsToTime($ss) {
		$hours = str_pad(floor(($ss%86400)/3600), 2, '0', STR_PAD_LEFT);
		$minutes = str_pad(floor(($ss%3600)/60), 2, '0', STR_PAD_LEFT);
		$seconds = str_pad($ss%60, 2, '0', STR_PAD_LEFT);
		$d = floor(($ss%2592000)/86400);
		return "$d Days $hours:$minutes:$seconds";
	}
	
	/**
	 * Converts bytes to a string representation including the type
	 *
	 * /DONE/ 
	 */	
	private function bytes($bytes) {
		$unim = array("B","KB","MB","GB","TB","PB");
		$c = 0;
		while ($bytes>=1024) {
			$c++;
			$bytes = $bytes/1024;
		}
		return number_format($bytes,($c ? 2 : 0),",",".")." ".$unim[$c];
	}		
	
	/**
	 * Validates that at least one node was selected when adding a package
	 *
	 * /DONE/ 
	 */
	public function validateNodeSet($nodes) {
		// Require at least one node
		return (isset($nodes[0]) && !empty($nodes[0]));
	}
	
	/**
	 * Validates that at least one template was selected when adding a package
	 *
	 * /DONE/ 
	 */
	public function validateTempaltesSet($templates) {
		// Require at least one node
		return (isset($templates[0]) && !empty($templates[0]));
	}
	
	/**
	 * Validates that the given hostname is valid
	 *
	 * /DONE/ 
	 */
	public function validateHostName($host_name) {
		if (strlen($host_name) > 255)
			return false;
		
		return $this->Input->matches($host_name, "/^([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9])(\.([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9]))+$/");
	}	
	
	/**
	 * Generates a password for Proxmox client accounts
	 *
	 * /DONE/ 
	 */
	private function generatePassword($min_chars = 12, $max_chars = 12) {
		$password = "";
		
		// Add 8-random characters
		$chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t',
		'u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',
		'S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9', '!', '@', '#', '$', '%',
		'^', '&', '*', '(', ')');
		$count = count($chars) - 1;
		$num_chars = (int)abs($min_chars == $max_chars ? $min_chars : mt_rand($min_chars, $max_chars));
		
		for ($i=0; $i<$num_chars; $i++)
			$password = $chars[mt_rand(0, $count)] . $password;
	 
		return $password;
	}	
	
	/**
	 * Converts bytes to a string representation including the type
	 *
	 * /NOT USED AT THE MOMENT/ 
	 
	private function convertBytesToString($bytes) {
		$step = 1024;
		$unit = "B";
		
		if (($value = number_format($bytes/($step*$step*$step), 2)) >= 1)
			$unit = "GB";
		elseif (($value = number_format($bytes/($step*$step), 2)) >= 1)
			$unit = "MB";
		elseif (($value = number_format($bytes/($step), 2)) >= 1)
			$unit = "KB";
		else
			$value = $bytes;
		
		return Language::_("Proxmox.!bytes.value", true, $value, $unit);
	}	
	*/
	
}
?>