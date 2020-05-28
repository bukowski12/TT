<?php

use Nette\Application\UI\Form;


class TarifTvPresenter extends BasePresenter
{

	/********************* view default *********************/


	public function renderDefault()
	{
			$this->template->tariftv = $this->tariftv->findAll()->order('price');
	}

/********************* views add & edit *********************/


	public function renderAdd()
	{
		$this['tarifForm']['save']->caption = 'Přidat';
	}

	public function renderEdit($id = 0)
	{
		$form = $this['tarifForm'];
		if (!$form->isSubmitted()) {
			$tariftv = $this->tariftv->findById($id);
			if (!$tariftv) {
				$this->error('Tarif nenalezen');
			}

			$form->setDefaults($tariftv);
		}
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->tariftv = $this->tariftv->findById($id);
		if (!$this->template->tariftv) {
			$this->error('Záznam nenalezen');
		}
	}


/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentTarifForm()
	{
		$form = new Form;
		$form->addText('name', 'Jméno:')
			->setRequired('Zadej jméno.');

		$form->addText('apicode', 'API Code:');

		$form->addText('price', 'Cena:')
		     ->addRule(Form::INTEGER, 'Cena musí být číslo')
			->setRequired('Zadej cenu.');
		
		$form->addText('description', 'Popis:');
			
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->tarifFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

	public function tarifFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$id = (int) $this->getParameter('id');
			if ($id) {
				$this->tariftv->findById($id)->update($values);
				$this->flashMessage('Tarif byl upraven.','success');
			} else {
				$this->tariftv->insert($values);
				$this->flashMessage('Tarif byl přidán.','success');
			}
			$this->redirect('default');
		}

/**
	 * Delete form factory.
	 * @return Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = $this->formCancelled;

		$form->addSubmit('delete', 'Delete')
			->setAttribute('class', 'default')
			->onClick[] = $this->deleteFormSucceeded;

		$form->addProtection();
		return $form;
	}


	public function deleteFormSucceeded()
	{
		try {
			$this->tariftv->findById($this->getParameter('id'))->delete();
			$this->flashMessage('Tarif byl smazán.','success');
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1451') !== FALSE) {
					$this->flashMessage('Tento tarif je používán, nejde smazat','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->flashMessage($e->getMessage());
		else throw $e;
    	}
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->redirect('default');
	}	
}