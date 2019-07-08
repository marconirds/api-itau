<?php

class Multa {

	private $tipo_multa;
	private $percentual_multa;


	public function getTipoMulta() {
		return $this->tipo_multa;
	}

	public function setTipoMulta($tipo_multa) {
		$this->tipo_multa = $tipo_multa;
	}

	public function setPercentualMulta($percentual_multa) {
		$this->percentual_multa = $percentual_multa;
	}

}