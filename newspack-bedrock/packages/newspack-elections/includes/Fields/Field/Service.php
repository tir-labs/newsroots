<?php

namespace Govpack\Fields\Field;

use Govpack\Fields\FieldType;
use Govpack\Fields\ProfileServiceRegistry;
use Govpack\Fields\Service as ProfileService;

class Service extends Link {

	protected ProfileService $service;

	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {
		parent::__construct( $slug, $label, 'service' );
	}

	public function class_in_namespace(string $class, string $namespace) : bool {
		
		// remove the leading "\" from namespaces, not needed when dynamic.
		// if using a string source from class::name it wont have a leading \
		// this normalises that against a class passed by a user that may be \Govpack\Something\Else
		$namespace = \ltrim($namespace, "\\"); // \\ is an escaped single
		$class = \ltrim($class, "\\");

		// if the classname passed starts with the namespace provided, strpos will be 0 and hence true
		return (strpos($class, $namespace) === 0);
	
	}

	public function set_service( string|ProfileService $service ): self {
		
		

		// an actual object of type Service was passed
		if ( is_a( $service, ProfileService::class ) ) {
			$this->service = $service;
		// a string representing an actual class eg \Full\Qualified\ClassName::class
		} else if( 	$this->class_in_namespace($service, "Govpack\\Fields\\Service") && \class_exists($service)){
			$this->service = new $service();
		
		// maybe a slug was passed, try get a class from it
		} else if(\class_exists("\\Govpack\\Fields\\Service\\" . $service)){
			$potential_full_class_name = "\\Govpack\\Fields\\Service\\" . $service;
			$this->service = new $potential_full_class_name();
		} else {
			$services = ProfileServiceRegistry::instance();
			if ( $services->exists( $service ) ) {
				$this->service = $services->get( $service );
			}
		}


		return $this;
	}

	
	public function to_array(): array {
		return [
			...parent::to_array(),
			'service'       => $this->service->slug(),
			'service_color' => $this->service->color(),
		];
	}

	

	public function service(): ProfileService {
		return $this->service;
	}
}
