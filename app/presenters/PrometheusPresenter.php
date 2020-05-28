<?php
class PrometheusPresenter extends BasePresenter
{
	
	private $path_conf;
	private $conf_file;
	private $include_file;
	private $prometheus_exec = "restart_prometheus.sh";

	public function startup()
	{
		parent::startup();
		$config = $this->settings->findById(1);
		if ($config->config != "NULL") {
			$settings = unserialize($config->config);
			$this->path_conf = $settings->pathconf;
			$this->conf_file = $settings->hostsfile;
			$this->include_file = $settings->hostsinc;
		}
	}

	public function renderDefault()
	{
		$file = $this->path_conf.$this->conf_file;
		if (file_exists($file)) {
			$handle = fopen($file, 'r');
			$fileContent = fread($handle, filesize($file));
			$fileContent = preg_replace('/\t/', '&nbsp;&nbsp;&nbsp;&nbsp;', $fileContent);
			//$fileContent = nl2br($fileContent);
			//$fileContent = htmlentities($fileContent);
			$this->template->conf_file = $fileContent;
			fclose($handle);
		} else {
    		$this->template->conf_file = "Nemohu nají soubor s konfigurací.";
		}
		$this->restart = NULL;
	}

	public function handleRestartPrometheus()
	{
		exec ('sudo '.$this->path_conf.$this->prometheus_exec);
	}
	
	public function actionCron()
    {
        if (!$this->getContext()->params['consoleMode']) {
            throw new AuthenticationException;
        }
        $this->handleGenerateConf(TRUE);
        $this->terminate();
    }

	public function handleGenerateConf($console=NULL)
	{
		$conf_file = "##########   File generate: ".date("H:i:s Y-m-d")."  ##########"."\n\n\n";
		$file = $this->path_conf.$this->include_file;
		if (file_exists($file)) {
			if (is_readable($file)) {
				$handle = fopen($file, 'r');
 				$fileContent = fread($handle,filesize($file));
 				$conf_file .= $fileContent;
			} else {
    			$this->flashMessage('Nemohu načíst soubor s konfigurací. '.$file,'error');
			}
		} else {
    		$this->flashMessage('Nemohu nají soubor s konfigurací. '.$file,'error');
		}	
		$routers = $this->router->findAll();
		foreach ($routers as $router_key) {
			$routerints = $this->routerint->findBy(array('router_id' => $router_key['idRouter']));
			foreach ($routerints as $routerint_key) {
					$clients = $this->client->findBy(array('routerint_id' => $routerint_key['idRouterInt'], 'valid' => '1'))->count();
					$conf_row = [];
					if ($clients > 0) {
						$clients = $this->client->findBy(array('routerint_id' => $routerint_key['idRouterInt'], 'valid' => '1'));
						$conf_file .= "\n\n\n";
						$conf_file .= "#####################################################################\n";
						$conf_file .= "##################     ".$router_key['name']."----".$routerint_key['name']."     #################\n";
						$conf_file .= "#####################################################################\n";
						$conf_file .= "\n\n";
						foreach ($clients as $client_key) {
							$customer = $this->customer->findBy(array('idCustomer' => $client_key['customer_id'], 'valid' => '1'))->fetch();
							if (!$customer['debtLocked']) {
								$tarif = $this->tarif->findBy(array('idTarif' => $client_key['tarif_id']))->fetch();
								$ip = $this->clientIP->findBy(array('client_id' => $client_key['idClient']))->order('ipAddress');
								if ($tarif['name']=='Shared') {
									$hostname = $this->findHostname($client_key['customer_id'],$client_key['idClient']);
									$enum = 100;
								} else {
									$hostname = $client_key['hostname'];
									$enum = 0;
								}
								#$conf_file .= "\n";
								foreach ($ip as $ip_key) {
									if ($enum == 0) {
										$conf_row[] = array ('ipaddress' => $ip_key['ipAddress'], 'text' => $ip_key['ipAddress']."\t".$client_key['hostname']."\t\t\t#{".$client_key['customer_id']."}via-prometheus-60-".$tarif['speed']."\n");
										#$conf_file .= $ip_key['ipAddress']."\t".$client_key['hostname']."\t\t\t#{".$client_key['customer_id']."}via-prometheus-60-".$tarif['speed']."\n";
									} else {
										$conf_row[] = array ('ipaddress' => $ip_key['ipAddress'], 'text' => $ip_key['ipAddress']."\t".$hostname.$enum."\t\t\t#sharing-".$hostname."\n");
										#$conf_file .= $ip_key['ipAddress']."\t".$hostname.$enum."\t\t\t#sharing-".$hostname."\n";
									}
									$enum++;
								}
							}
						}
						unset($ip_addresses);
						foreach($conf_row as $key => $value){ 
							$ip_addresses[$key] = $value["ipaddress"]; 
						} 
						if (!empty($ip_addresses)) {
							array_multisort($ip_addresses, SORT_ASC, SORT_NATURAL, $conf_row); 
						}
						foreach ($conf_row as $row) {
							$conf_file .= $row['text'];
						}
						unset($conf_row);
					}
			}
		}
		$file = $this->path_conf.$this->conf_file;
		if ($handle = fopen($file, 'w')) {
			if (fwrite($handle, $conf_file) === FALSE) {
				$this->template->conf_file = "Nemohu uložit konfiguraci.";
			}
			$this->template->conf_file = $conf_file;
			fclose($handle);
		}
		if ($console) {
			$this->terminate();
		}
		$this->redirect('default');
	}

	public function findHostname ($idCustomer, $noClient)
	{
		$clients = $this->client->findBy(array('customer_id' => $idCustomer, 'valid' => '1'));
		foreach ($clients as $client_key) {
			if ($client_key['idClient']!= $noClient) {
				return ($client_key['hostname']);
			}
		}
	}
}

