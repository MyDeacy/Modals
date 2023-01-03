<?php

declare(strict_types=1);

namespace rarkhopper\modals\custom;

use pocketmine\player\Player;
use rarkhopper\modals\custom\element\CustomFormElements;
use rarkhopper\modals\custom\element\DropDown;
use rarkhopper\modals\custom\element\ICustomFormOption;
use rarkhopper\modals\custom\element\Input;
use rarkhopper\modals\custom\element\Label;
use rarkhopper\modals\custom\element\Slider;
use rarkhopper\modals\custom\element\StepSlider;
use rarkhopper\modals\custom\element\Toggle;
use rarkhopper\modals\FormBase;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function var_dump;

abstract class CustomFormBase extends FormBase{
	private CustomFormElements $elements;

	abstract protected function onSubmit(Player $player, CustomFormResponse $response) : void;

	public function __construct(CustomFormElements $elements){
		$this->elements = $elements;
	}

	protected function getElements() : CustomFormElements{
		return $this->elements;
	}

	protected function internalHandleResponse(Player $player, int|bool|array $rawResponse) : void{
		if(!is_array($rawResponse)) return;
		$response = $this->createResponse($rawResponse);

		if($response === null) return;
		$this->onSubmit($player, $response);
	}

	/**
	 * @param array<int, int|string|bool> $rawResponse
	 */
	private function createResponse(array $rawResponse) : ?CustomFormResponse{
		$responses = [];
		$options = $this->getElements()->getOptions()->getAll();

		foreach($rawResponse as $idx => $raw){
			$option = $options[$idx] ?? null;

			if($option === null) return null;
			if(!$this->validateResponse($option, $raw)) return null;
			$responses[$option->getName()] = $raw;
		}
		return new CustomFormResponse($responses, $rawResponse);
	}

	private function validateResponse(ICustomFormOption $option, mixed $rawResponse) : bool{
		if($option instanceof DropDown){
			if(!is_int($rawResponse)) return false;
			return isset($option->getOptions()[$rawResponse]);

		}elseif($option instanceof Input){
			if(is_string($rawResponse)) return true;

		}elseif($option instanceof Label){
			var_dump($rawResponse); //TODO: dump

		}elseif($option instanceof Slider){
			if(!is_int($rawResponse)) return false;
			return $option->getMin() <= $rawResponse && $rawResponse <= $option->getMax();

		}elseif($option instanceof StepSlider){
			if(!is_int($rawResponse)) return true;
			return isset($option->getSteps()[$rawResponse]);

		}elseif($option instanceof Toggle){
			if(is_bool($rawResponse)) return true;
		}
		return false;
	}
}
