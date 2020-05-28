<?php
use Nette\Application\UI\Presenter;
use Nette\Application\UI;


class SignPresenter extends Presenter
{
	/** @persistent */
	public $backlink = '';


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('login', 'Login:')
			->setRequired('Zadej login.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadej heslo.');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		try {
			$values = $form->getValues();
			$this->getUser()->login($values->login, $values->password);

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}
		$this->restoreRequest($this->backlink);
		$this->redirect('Dashboard:');
	}

	protected function createComponentChangePassForm()
	{
		$form = new UI\Form;

        $form->addPassword('oldPassword', 'Staré heslo:', 30)   
			->setRequired('Zadej staré heslo.');

		$form->addPassword('newPassword', 'Nové heslo:', 30)
			->addRule(UI\Form::MIN_LENGTH, 'Nové heslo musí mít alespoň %d znaků.', 6);

		$form->addPassword('confirmPassword', 'Potvrzení hesla:', 30)
            ->addRule(UI\Form::FILLED, 'Nové heslo je nutné zadat ještě jednou pro potvrzení.')
            ->addRule(UI\Form::EQUAL, 'Zadná hesla se musejí shodovat.', $form['newPassword']);

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = $this->changePassFormSucceeded;
		return $form;
	}


	public function changePassFormSucceeded($form)
	{
		try {
			$values = $form->getValues();
			$this->user->authenticator->changePass($this->getUser()->getIdentity()->login, $values->oldPassword, $values->newPassword, $values->confirmPassword);

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->getUser()->logout();
		$this->flashMessage('Heslo bylo změněno. Přihlašte se znovu.', 'success');
		$this->redirect('in');
	}
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.', 'success');
		$this->redirect('in');
	}

}
