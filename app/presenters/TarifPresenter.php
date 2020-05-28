<?php

use Nette\Application\UI\Form;


class TarifPresenter extends BasePresenter
{

	/********************* view default *********************/


	public function renderDefault()
	{
			$this->template->tarif = $this->tarif->findAll()->order('speed');
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
			$tarif = $this->tarif->findById($id);
			if (!$tarif) {
				$this->error('Tarif nenalezen');
			}

			$form->setDefaults($tarif);
		}
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->tarif = $this->tarif->findById($id);
		if (!$this->template->tarif) {
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

		$form->addText('speed', 'Rychlost:')
		    ->addRule(Form::INTEGER, 'Rychlost musí být číslo')
			->setRequired('Zadej rychlost.');

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
				$this->tarif->findById($id)->update($values);
				$this->flashMessage('Tarif byl upraven.','success');
			} else {
				$this->tarif->insert($values);
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
			$this->tarif->findById($this->getParameter('id'))->delete();
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