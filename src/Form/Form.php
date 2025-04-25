<?php


namespace App\UI\Form;


use App\Common\Hash;
use App\Common\Log;
use App\Common\str;
use App\Language\Language;
use App\Translation\Translator;
use App\UI\Button;
use App\UI\Grid;

/**
 * Class Form
 *
 * Generates form HTML.
 *
 * @package App\UI\Form
 */
class Form {
	/**
	 * The ID of the form
	 * @var string
	 */
	private $id;

	/**
	 * @var Hash
	 */
	private $hash;

	/**
	 * @var array
	 */
	private $fields;

	/**
	 * @var array
	 */
	private $buttons;

	/**
	 * @var string
	 */
	private $script;

	/**
	 * @var array
	 */
	private $class;

	/**
	 * @var array
	 */
	private $style;

	/**
	 * Meta properties.
	 * @var string
	 */
	private $action;
	private $rel_table;
	private $rel_id;
	private $callback;
	private $modal;
	private $data;

	/**
	 * If set, will translate every form and button to that language.
	 *
	 * @var mixed
	 */
	private ?string $language_id = NULL;


	/**
	 * Form constructor.
	 * ```
	 * $form = new Form([
	 *    "action" => "insert",
	 *    "rel_table" => NULL,
	 *    "rel_id" => NULL,
	 *    "fields" => $fields,
	 *    "buttons" => $buttons
	 * ]);
	 * ```
	 *
	 * @param null $a
	 */
	public function __construct($a = NULL)
	{
		$this->hash = Hash::getInstance();

		# Empty form
		if(!is_array($a)){
			return true;
		}

		extract($a);

		# Form ID
		$this->setId($id);
		// Every form must have an ID

		# Meta fields
		$this->language_id = $language_id;
		$this->action = $action;
		$this->rel_table = $rel_table;
		$this->rel_id = $rel_id;
		$this->callback = $callback;

		# Data field
		$this->data = $data;

		# Is it in a modal?
		$this->modal = $modal;

		# Is it in a card?
		$this->card = $card;

		# Fields
		$this->setFields($fields);

		# Buttons
		$this->setButtons($buttons);

		# Script
		$this->setScript($script);

		# Encryption required?
		$this->setEncrypt($encrypt);

		# Class
		$this->class = str::getAttrArray($class, [], $only_class);

		# Style
		$this->style = str::getAttrArray($style, []);

		return true;
	}

	/**
	 * Sets scripts
	 *
	 * @param string $script
	 *
	 * @return bool
	 */
	function setScript($script)
	{
		//		$this->script = /** @lang ECMAScript 6 */<<<EOF
		//
		//var {$this->getId()}_form_is_valid = $("#{$this->getId()}").validate(validationSettings);
		//$('#{$this->getId()}').submit(function(event){
		//    event.preventDefault();
		//    if({$this->getId()}_form_is_valid.form()){
		//		submitForm(event, "{$this->getId()}");
		//    } else {
		//        Ladda.stopAll();
		//    }
		//});
		//
		//EOF;
		if($script){
			$this->script = $script;
		}

		//		if($only_script){
		//			$this->script = $only_script;
		//		}

		return true;
	}

	/**
	 * Add buttons to the form.
	 * Can be a single button.
	 *
	 * @param $buttons
	 *
	 * @return bool
	 */
	public function setButtons($buttons)
	{
		if(!$buttons){
			return true;
		}
		if(str::isNumericArray($buttons)){
			foreach($buttons as $button){
				$this->buttons[] = $button;
			}
		}
		else {
			$this->buttons[] = $buttons;
		}
		return true;
	}

	/**
	 * Add fields to the form.
	 * Can be a single field,
	 * or a numeric array of many fields
	 *
	 * @param $fields
	 *
	 * @return bool
	 */
	public function setFields($fields)
	{
		if($fields === false){
			$this->fields = [];
			return true;
		}

		if(!$fields){
			return true;
		}

		if(str::isNumericArray($fields)){
			foreach($fields as $field){
				$this->fields[] = $field;
			}
		}
		else {
			$this->fields[] = $fields;
		}

		return true;
	}

	/**
	 * If form fields are to be encrypted before being sent,
	 * include a "encrypt" => [] with names of all the fields.
	 *
	 * This is to prevent the unencrypted field value from being stored in
	 * access logs, error logs and audit trails.
	 *
	 * @param null $encrypt
	 *
	 * @return bool
	 */
	public function setEncrypt($encrypt = NULL): bool
	{
		if(!$encrypt){
			return false;
		}
		$encrypt = is_array($encrypt) ? $encrypt : [$encrypt];

		# Create a key pair (public and private keys)
		$keypair = sodium_crypto_box_keypair();

		# Grab a hex of the public key
		$public_key = sodium_bin2hex(sodium_crypto_box_publickey($keypair));

		# Store the key pair in a session variable
		$_SESSION['pgp'][$public_key] = $keypair;

		# Store the public key as a form field
		$this->setFields([
			"type" => "hidden",
			"name" => "meta_public_key",
			"value" => $public_key,
		]);

		# Store the names of the fields to encrypt, as its own form field
		$this->setFields([
			"type" => "hidden",
			"name" => "meta_encrypt",
			"value" => json_encode($encrypt),
		]);

		return true;
	}

	/**
	 * Given an array of vars (returned from a form),
	 * will decrypt those fields that require it.
	 *
	 * Assumes the user who instigated the form is the same
	 * that is asking for decryption, as the keys are stored
	 * in the $_SESSION.
	 *
	 * @param $vars
	 *
	 * @return bool Will return false if there is an issue.
	 * @throws \Exception
	 */
	public static function decryptVars(&$vars): bool
	{
		# Ensure there are vars to be decrypted
		if(!$vars['meta_public_key'] || !$vars['meta_encrypt']){
			//If there is nothing to be decrypted
			return true;
		}

		# Ensure the key is valid
		if(!$_SESSION['pgp'][$vars['meta_public_key']]){
			// If the key is not valid (it's because the page needs refreshing)

			# Request a page reload
			$hash = Hash::getInstance();
			$hash->set("reload");

			Log::getInstance()->warning([
				"title" => "Timed out form",
				"message" => "There was an issue with the encrypted form you just submitted.
				This is most probably due to the form being open for too long.
				The page will now refresh, please try again. Apologies for the inconvenience.",
			]);

			return false;
		}

		# For each variable, decrypt and save the value
		foreach(json_decode($vars['meta_encrypt'], true) as $key){
			try {
				$vars[$key] = sodium_crypto_box_seal_open(sodium_hex2bin($vars[$key]), $_SESSION['pgp'][$vars['meta_public_key']]);
			}
			catch(\SodiumException $e) {
				// If the key is not valid (it's because the page needs refreshing)

				# Request a page reload
				$hash = Hash::getInstance();
				$hash->set("reload");

				Log::getInstance()->warning([
					"title" => "Timed out form",
					"message" => "There was an issue with the encrypted form you just submitted.
					This is most probably due to the form being open for too long.
					The page will now refresh, please try again. Apologies for the inconvenience.",
				]);

				return false;
			}
		}
		return true;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	private function setId($id = NULL)
	{
		$this->id = $id ?: str::id("form");
		return $this->id;
	}

	/**
	 * Returns the ID either as only the ID,
	 * or as a tag.
	 *
	 * @param bool $tag If set to TRUE will return the id as a tag.
	 *
	 * @return string
	 */
	function getId($tag = NULL)
	{
		if($tag){
			return str::getAttrTag("id", $this->getId());
		}
		return $this->id ?: $this->setId();
	}

	private function tabsAreInUse(): bool
	{
		if(!$this->fields){
			return false;
		}

		foreach($this->fields as $field){
			if($field['tabs']){
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns an array of form fields as HTML.
	 * Will not return any form wrapping.
	 *
	 * @param array|null $fields
	 *
	 * @return string|null
	 */
	public static function getFieldsAsHtml(?array $fields): ?string
	{
		if(!$fields){
			return NULL;
		}

		$form = new Form([
			"fields" => $fields,
		]);

		return $form->getFieldsHTML();
	}

	/**
	 * Fields are generated using the Field() class,
	 * and placed in a grid using the Grid() class.
	 *
	 * This method is merely a junction method.
	 *
	 * @param null $fields
	 *
	 * @return bool|string
	 */
	function getFieldsHTML($fields = NULL)
	{
		$this->setFields($fields);

		$grid = new Grid([
			"formatter" => function(&$field){
				return Field::getHTML($field);
			},
		]);

		# Slightly hacky way to add a class to the modal tab header to allow for draggable (if enabled)
		if($this->tabsAreInUse() && $this->modal){
			$this->fields = [[
				"tabs" => [
					"id" => reset($this->fields)['id'],
					"tabs" => reset($this->fields)['tabs'],
					"class" => "modal-header-draggable",
				],
			]];
		}

		# Remove blanks
		if(!$this->fields = array_filter($this->fields ?:[])){
			return NULL;
		}

		# Translate each field (if a language ID is provided)
		foreach($this->fields as &$field){
			if(class_exists("App\\Translation\\Translator")){
				Translator::set($field, [
					"rel_table" => "form_field",
					"to_language_id" => $this->language_id,
				]);
			}
		}

		return $grid->getHTML($this->fields);
	}

	/**
	 * Prepares form buttons for being used in modals.
	 * If the modal flag is set to true, will move the buttons
	 * to the footer to make the modal more aesthetically pleasing.
	 *
	 * Needs to be here instead in the Button() class, because it uses
	 * many form attributes.
	 *
	 * @param array|string|null $a
	 */
	private function prepareButtonsForModals(&$a): void
	{
		if(str::isNumericArray($a)){
			foreach($a as $id => $button){
				$this->prepareButtonsForModals($a[$id]);
			}
		}

		if(str::isAssociativeArray($a)){
			if($a['type'] == "submit"){
				$a['form'] = $this->getId();
			}
			if(str::isNumericArray($a['split'])){
				$this->prepareButtonsForModals($a['split']);
			}
		}

		if(is_string($a)){
			if($a = Button::COMMON[$a]){
				$this->prepareButtonsForModals($a);
			}
		}
	}

	public function getModalButtonsHTML(): string
	{
		$buttons_html = Button::generate($this->buttons);

		return <<<EOF
		<div class="container">
			<div class="footer-content"></div>
			<div class="btn-float-right">
				{$buttons_html}
			</div>
		</div>
EOF;
	}

	/**
	 * Given buttons and a language ID, will set the language
	 * ID for each button.
	 *
	 * @param array|null  $buttons
	 * @param string|null $language_id
	 *
	 * @return void
	 */
	public static function setButtonLanguageId(?array &$buttons, ?string $language_id): void
	{
		if(!$language_id){
			return;
		}

		if(!$buttons){
			return;
		}

		foreach($buttons as &$button){
			if(!$button){
				continue;
			}

			if(is_string($button)){
				$button = [
					"common" => $button,
				];
			}

			$button['language_id'] = $language_id;
		}
	}

	/**
	 * A bit hacky, but works for now.
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Exception
	 */
	public function getButtonsHTML()
	{
		if(!$this->buttons){
			return false;
		}

		# Add the language ID to each button
		self::setButtonLanguageId($this->buttons, $this->language_id);

		if($this->modal || $this->card){
			$this->prepareButtonsForModals($this->buttons);
		}

		$buttons_html = Button::generate($this->buttons);

		if($this->modal){
			if(is_string($this->modal)){
				$id = str::getAttrTag("id", $this->modal);
			}
			$class = $this->tabsAreInUse() ? "modal-footer-tabs" : "modal-footer";
			$buttons_html = <<<EOF
	</div>
</div>
<div class="{$class}"{$id}>
	{$this->getModalButtonsHTML()}	
	<div style="display: none;">
EOF;
		}

		if($this->card){
			$buttons_html = <<<EOF
	</div>
</div>
<div class="card-footer">
	<div class="container">
		<div class="footer-content"></div>
		<div class="btn-float-right">
			{$buttons_html}
		</div>
	</div>
	<div style="display: none;">
EOF;
		}

		return <<<EOF
<div class="btn-float-right">
	{$buttons_html}
</div>
EOF;
	}

	/**
	 * @return bool|string
	 */
	public function getScriptHTML()
	{
		return str::getScriptTag($this->script);
	}

	public function getModalBodyHTML(): string
	{
		$id = str::getAttrTag("id", $this->id);
		$class_array = str::getAttrArray($this->class, ["modal-body"]);
		$class_array[] = Language::getDirectionClass($this->language_id);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		$data = str::getDataAttr($this->data);

		return /** @lang HTML */ <<<EOF
<form method="POST"{$id}{$data}{$class}{$style}>
	<input type="hidden" name="meta_action" value="{$this->action}"/>
	<input type="hidden" name="meta_rel_table" value="{$this->rel_table}"/>
	<input type="hidden" name="meta_rel_id" value="{$this->rel_id}"/>
	<input type="hidden" name="callback" value="{$this->callback}"/>
	{$this->getFieldsHTML()}
</form>
{$this->getScriptHTML()}
EOF;
	}

	/**
	 * Returns the form as HTML.
	 *
	 * @return string
	 */
	public function getHTML()
	{
		$id = str::getAttrTag("id", $this->id);
		$class_array = str::getAttrArray($this->class);
		$class_array[] = Language::getDirectionClass($this->language_id);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		$data = str::getDataAttr($this->data);

		return /** @lang HTML */ <<<EOF
<form method="POST"{$id}{$data}{$class}{$style}>
	<input type="hidden" name="meta_action" value="{$this->action}"/>
	<input type="hidden" name="meta_rel_table" value="{$this->rel_table}"/>
	<input type="hidden" name="meta_rel_id" value="{$this->rel_id}"/>
	<input type="hidden" name="callback" value="{$this->callback}"/>
	{$this->getFieldsHTML()}
	{$this->getButtonsHTML()}
</form>
{$this->getScriptHTML()}
EOF;
	}
}