<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Loaders;

use Nette;


/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 * @deprecated
 */
class NetteLoader
{
	/** @var NetteLoader */
	private static $instance;

	/** @var array */
	public $renamed = array(
		'Nette\Config\Configurator' => 'Nette\Configurator',
		'Nette\Config\CompilerExtension' => 'Nette\DI\CompilerExtension',
		'Nette\Http\User' => 'Nette\Security\User',
		'Nette\Templating\DefaultHelpers' => 'Nette\Templating\Helpers',
		'Nette\Templating\FilterException' => 'Latte\CompileException',
		'Nette\Utils\PhpGenerator\ClassType' => 'Nette\PhpGenerator\ClassType',
		'Nette\Utils\PhpGenerator\Helpers' => 'Nette\PhpGenerator\Helpers',
		'Nette\Utils\PhpGenerator\Method' => 'Nette\PhpGenerator\Method',
		'Nette\Utils\PhpGenerator\Parameter' => 'Nette\PhpGenerator\Parameter',
		'Nette\Utils\PhpGenerator\PhpLiteral' => 'Nette\PhpGenerator\PhpLiteral',
		'Nette\Utils\PhpGenerator\Property' => 'Nette\PhpGenerator\Property',
		'Nette\Diagnostics\Bar' => 'Tracy\Bar',
		'Nette\Diagnostics\BlueScreen' => 'Tracy\BlueScreen',
		'Nette\Diagnostics\DefaultBarPanel' => 'Tracy\DefaultBarPanel',
		'Nette\Diagnostics\Dumper' => 'Tracy\Dumper',
		'Nette\Diagnostics\FireLogger' => 'Tracy\FireLogger',
		'Nette\Diagnostics\Logger' => 'Tracy\Logger',
		'Nette\Diagnostics\OutputDebugger' => 'Tracy\OutputDebugger',
		'Nette\Latte\ParseException' => 'Latte\CompileException',
		'Nette\Latte\CompileException' => 'Latte\CompileException',
		'Nette\Latte\Compiler' => 'Latte\Compiler',
		'Nette\Latte\HtmlNode' => 'Latte\HtmlNode',
		'Nette\Latte\IMacro' => 'Latte\IMacro',
		'Nette\Latte\MacroNode' => 'Latte\MacroNode',
		'Nette\Latte\MacroTokens' => 'Latte\MacroTokens',
		'Nette\Latte\Parser' => 'Latte\Parser',
		'Nette\Latte\PhpWriter' => 'Latte\PhpWriter',
		'Nette\Latte\Token' => 'Latte\Token',
		'Nette\Latte\Macros\CoreMacros' => 'Latte\Macros\CoreMacros',
		'Nette\Latte\Macros\MacroSet' => 'Latte\Macros\MacroSet',
		'Nette\Latte\Macros\CacheMacro' => 'Nette\Bridges\CacheLatte\CacheMacro',
		'Nette\Latte\Macros\FormMacros' => 'Nette\Bridges\FormsLatte\FormMacros',
		'Nette\Latte\Macros\UIMacros' => 'Nette\Bridges\ApplicationLatte\UIMacros',
		'Nette\ArrayHash' => 'Nette\Utils\ArrayHash',
		'Nette\ArrayList' => 'Nette\Utils\ArrayList',
		'Nette\DateTime' => 'Nette\Utils\DateTime',
		'Nette\Image' => 'Nette\Utils\Image',
		'Nette\ObjectMixin' => 'Nette\Utils\ObjectMixin',
		'Nette\Utils\NeonException' => 'Nette\Neon\Exception',
		'Nette\Utils\NeonEntity' => 'Nette\Neon\Entity',
		'Nette\Utils\Neon' => 'Nette\Neon\Neon',
	);

	/** @var array */
	public $list = array(
		'Latte\CompileException' => 'Latte/exceptions',
		'Latte\Compiler' => 'Latte/Compiler/Compiler',
		'Latte\Engine' => 'Latte/Engine',
		'Latte\Helpers' => 'Latte/Helpers',
		'Latte\HtmlNode' => 'Latte/Compiler/HtmlNode',
		'Latte\ILoader' => 'Latte/ILoader',
		'Latte\IMacro' => 'Latte/IMacro',
		'Latte\Loaders\FileLoader' => 'Latte/Loaders/FileLoader',
		'Latte\Loaders\StringLoader' => 'Latte/Loaders/StringLoader',
		'Latte\MacroNode' => 'Latte/Compiler/MacroNode',
		'Latte\MacroTokens' => 'Latte/Compiler/MacroTokens',
		'Latte\Macros\BlockMacros' => 'Latte/Macros/BlockMacros',
		'Latte\Macros\CoreMacros' => 'Latte/Macros/CoreMacros',
		'Latte\Macros\MacroSet' => 'Latte/Macros/MacroSet',
		'Latte\Parser' => 'Latte/Compiler/Parser',
		'Latte\PhpHelpers' => 'Latte/Compiler/PhpHelpers',
		'Latte\PhpWriter' => 'Latte/Compiler/PhpWriter',
		'Latte\RegexpException' => 'Latte/exceptions',
		'Latte\RuntimeException' => 'Latte/exceptions',
		'Latte\Runtime\CachingIterator' => 'Latte/Runtime/CachingIterator',
		'Latte\Runtime\FilterExecutor' => 'Latte/Runtime/FilterExecutor',
		'Latte\Runtime\FilterInfo' => 'Latte/Runtime/FilterInfo',
		'Latte\Runtime\Filters' => 'Latte/Runtime/Filters',
		'Latte\Runtime\Html' => 'Latte/Runtime/Html',
		'Latte\Runtime\IHtmlString' => 'Latte/Runtime/IHtmlString',
		'Latte\Runtime\ISnippetBridge' => 'Latte/Runtime/ISnippetBridge',
		'Latte\Runtime\SnippetDriver' => 'Latte/Runtime/SnippetDriver',
		'Latte\Runtime\Template' => 'Latte/Runtime/Template',
		'Latte\Strict' => 'Latte/Strict',
		'Latte\Token' => 'Latte/Compiler/Token',
		'Latte\TokenIterator' => 'Latte/Compiler/TokenIterator',
		'Latte\Tokenizer' => 'Latte/Compiler/Tokenizer',
		'NetteModule\ErrorPresenter' => 'Application/ErrorPresenter',
		'NetteModule\MicroPresenter' => 'Application/MicroPresenter',
		'Nette\Application\AbortException' => 'Application/exceptions',
		'Nette\Application\ApplicationException' => 'Application/exceptions',
		'Nette\Application\BadRequestException' => 'Application/exceptions',
		'Nette\Application\ForbiddenRequestException' => 'Application/exceptions',
		'Nette\Application\InvalidPresenterException' => 'Application/exceptions',
		'Nette\ArgumentOutOfRangeException' => 'Utils/exceptions',
		'Nette\Caching\Storages\PhpFileStorage' => 'deprecated/Caching/PhpFileStorage',
		'Nette\Callback' => 'deprecated/Callback',
		'Nette\Configurator' => 'Bootstrap/Configurator',
		'Nette\DI\MissingServiceException' => 'DI/exceptions',
		'Nette\DI\ServiceCreationException' => 'DI/exceptions',
		'Nette\Database\ConnectionException' => 'Database/exceptions',
		'Nette\Database\ConstraintViolationException' => 'Database/exceptions',
		'Nette\Database\ForeignKeyConstraintViolationException' => 'Database/exceptions',
		'Nette\Database\NotNullConstraintViolationException' => 'Database/exceptions',
		'Nette\Database\UniqueConstraintViolationException' => 'Database/exceptions',
		'Nette\DeprecatedException' => 'Utils/exceptions',
		'Nette\Diagnostics\Debugger' => 'deprecated/Diagnostics/Debugger',
		'Nette\Diagnostics\Helpers' => 'deprecated/Diagnostics/Helpers',
		'Nette\Diagnostics\IBarPanel' => 'deprecated/Diagnostics/IBarPanel',
		'Nette\DirectoryNotFoundException' => 'Utils/exceptions',
		'Nette\Environment' => 'deprecated/Environment',
		'Nette\FileNotFoundException' => 'Utils/exceptions',
		'Nette\FreezableObject' => 'deprecated/Utils/FreezableObject',
		'Nette\IFreezable' => 'deprecated/Utils/IFreezable',
		'Nette\IOException' => 'Utils/exceptions',
		'Nette\InvalidArgumentException' => 'Utils/exceptions',
		'Nette\InvalidStateException' => 'Utils/exceptions',
		'Nette\Latte\Engine' => 'deprecated/Latte/Engine',
		'Nette\LegacyObject' => 'Utils/LegacyObject',
		'Nette\Loaders\RobotLoader' => 'RobotLoader/RobotLoader',
		'Nette\Localization\ITranslator' => 'Utils/ITranslator',
		'Nette\Mail\FallbackMailerException' => 'Mail/exceptions',
		'Nette\Mail\SendException' => 'Mail/exceptions',
		'Nette\Mail\SmtpException' => 'Mail/exceptions',
		'Nette\MemberAccessException' => 'Utils/exceptions',
		'Nette\NotImplementedException' => 'Utils/exceptions',
		'Nette\NotSupportedException' => 'Utils/exceptions',
		'Nette\OutOfRangeException' => 'Utils/exceptions',
		'Nette\SmartObject' => 'Utils/SmartObject',
		'Nette\StaticClass' => 'Utils/StaticClass',
		'Nette\StaticClassException' => 'Utils/exceptions',
		'Nette\Templating\FileTemplate' => 'deprecated/Templating/FileTemplate',
		'Nette\Templating\Helpers' => 'deprecated/Templating/Helpers',
		'Nette\Templating\IFileTemplate' => 'deprecated/Templating/IFileTemplate',
		'Nette\Templating\ITemplate' => 'deprecated/Templating/ITemplate',
		'Nette\Templating\Template' => 'deprecated/Templating/Template',
		'Nette\UnexpectedValueException' => 'Utils/exceptions',
		'Nette\Utils\AssertionException' => 'Utils/exceptions',
		'Nette\Utils\ImageException' => 'Utils/exceptions',
		'Nette\Utils\JsonException' => 'Utils/exceptions',
		'Nette\Utils\LimitedScope' => 'deprecated/Utils/LimitedScope',
		'Nette\Utils\MimeTypeDetector' => 'deprecated/Utils/MimeTypeDetector',
		'Nette\Utils\RegexpException' => 'Utils/exceptions',
		'Nette\Utils\SafeStream' => 'SafeStream/SafeStream',
		'Nette\Utils\UnknownImageFileException' => 'Utils/exceptions',
		'Tracy\Bar' => 'Tracy/Bar',
		'Tracy\BlueScreen' => 'Tracy/BlueScreen',
		'Tracy\Bridges\Nette\Bridge' => 'Bridges/Nette/Bridge',
		'Tracy\Bridges\Nette\MailSender' => 'Bridges/Nette/MailSender',
		'Tracy\Bridges\Nette\TracyExtension' => 'Bridges/Nette/TracyExtension',
		'Tracy\Debugger' => 'Tracy/Debugger',
		'Tracy\DefaultBarPanel' => 'Tracy/DefaultBarPanel',
		'Tracy\Dumper' => 'Tracy/Dumper',
		'Tracy\FireLogger' => 'Tracy/FireLogger',
		'Tracy\Helpers' => 'Tracy/Helpers',
		'Tracy\IBarPanel' => 'Tracy/IBarPanel',
		'Tracy\ILogger' => 'Tracy/ILogger',
		'Tracy\Logger' => 'Tracy/Logger',
		'Tracy\OutputDebugger' => 'Tracy/OutputDebugger',
	);


	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return static
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new static;
		}
		return self::$instance;
	}


	/**
	 * Register autoloader.
	 * @param  bool  prepend autoloader?
	 * @return void
	 */
	public function register($prepend = false)
	{
		spl_autoload_register(array($this, 'tryLoad'), true, (bool) $prepend);
	}


	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim($type, '\\');
		if (isset($this->renamed[$type])) {
			class_alias($this->renamed[$type], $type);
			trigger_error("Class $type has been renamed to {$this->renamed[$type]}.", E_USER_WARNING);

		} elseif (isset($this->list[$type])) {
			require __DIR__ . '/../' . $this->list[$type] . '.php';

		} elseif (substr($type, 0, 6) === 'Nette\\' && is_file($file = __DIR__ . '/../' . strtr(substr($type, 5), '\\', '/') . '.php')) {
			require $file;
		}
	}
}
