<?php

use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
	protected $client;
	protected $clienttv;
	protected $customer;
	protected $tarif;
	protected $tariftv;
	protected $inactive;
	protected $router;
	protected $routerint;
	protected $clientip;
	protected $payment;
	protected $clientIP;
	protected $settings;
	protected $trafficlog;
	protected $log;
	protected $logger;

	/** @persistent */
    public $backlink = '';
    /** @persistent */
	public $restart = NULL;

    protected function startup()
	{
		parent::startup();

		if (!$this->getContext()->params['consoleMode']) {
			if (!$this->user->isLoggedIn()) {
				if ($this->user->logoutReason === Nette\Http\UserStorage::INACTIVITY) {
					$this->flashMessage('Byl jste odhlášen z důvodu neaktivity. Prosím přihlaste se znovu.');
				}
				$this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
				$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Uživatel se přihlásil');
			}
		}
	}

	protected function beforeRender()
    {
    	$this->template->restart = $this->restart;
    }

	public function inject(ClientRepository $client, ClienttvRepository $clienttv, CustomerRepository $customer, TarifRepository $tarif, TarifTvRepository $tariftv,
						ClientInactiveRepository $inactive, RouterRepository $router, RouterIntRepository $routerint,
						ClientIPRepository $clientip, PaymentRepository $payment, ClientIPRepository $clientIP, SettingsRepository $settings,
						TrafficLogRepository $trafficlog, LogRepository $log, Logger $logger)
	{
		$this->client = $client;
		$this->clienttv = $clienttv;
		$this->customer = $customer;
		$this->tarif = $tarif;
		$this->tariftv = $tariftv;
		$this->inactive = $inactive;
		$this->router = $router;
		$this->routerint = $routerint;
		$this->clientip = $clientip;
		$this->payment = $payment;
		$this->clientIP = $clientIP;
		$this->settings = $settings;
		$this->trafficlog = $trafficlog;
		$this->log = $log;
		$this->logger = $logger;
	}
}
