<?php

use Nette\Application\UI\Form;
use Nette\Utils\Html;

class LogPresenter extends BasePresenter
{
	public $search;
	public $log;


	protected function createComponentPaginator()
	{
		$visualPaginator = new VisualPaginator();
		$visualPaginator->paginator->itemsPerPage = 100;
		return $visualPaginator;
	}


	public function handleSearch($value = NULL,$invalid_button = NULL)
	{
		$paginator = $this['paginator']->getPaginator();
		$this->search = $value;
		$this->invalid = $invalid_button;
		if (isset($value) || isset($invalid_button)) {
			$this->search = $value; 
			$paginator->itemCount = $this->customer->findCustomerLike($value, ($this->invalid == NULL)?'1':'0')->count();
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findCustomerLike($value, ($this->invalid == NULL)?'1':'0')->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
		}else{
			$paginator->itemCount = $this->customer->findBy(array('valid' => $invalid =='checked'?'0':'1'))->count();
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findBy(array('valid' => $invalid =='checked'?'0':'1'))->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
		}
	}


	/********************* view default *********************/


	public function renderDefault($sort = NULL)
	{
		if (!isset($this->template->customer)) {
			$paginator = $this['paginator']->getPaginator();
			// create visual paginator control
			$paginator->itemCount = $this->log->findAll()->count();
			$this->template->log = $this->log->findAll()->order('timestamp DESC')->limit($paginator->itemsPerPage, $paginator->offset);
			}
	}

/********************* views add & edit *********************/


	static function addLog($user, $type, $desc)
	{
		$values = array('user_id' => $user, 'type' => $type, 'description' => $desc);
		$this->log->insert($values);
	}

	
	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->getElementPrototype()->class('ajax');
		
		$form->addText('searchtext', 'Hledej')
			->setType('search')
			->getControlPrototype()
			->onkeyup("$(this).ajaxSubmit();");

		$form->addCheckbox('invalid', 'Vypsat neplatné');

        $form->onSuccess[] = callback($this, 'processSearchForm');
		return $form;
	}

	public function processSearchForm($form){

            $values = $form->values;
            $this->invalidateControl('searchtable');
            $this->template->customer = $this->customer->findCustomerLike($values['searchtext'])->order('surname')->order('name');
        }

	public function formCancelled()
	{
		$this->redirect('default');
	}	
}
