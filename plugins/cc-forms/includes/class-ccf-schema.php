<?php
/**
 * Esquema JSON de un formulario: etapas, campos, condiciones y validación.
 *
 * PHP puro, sin WordPress, para poder probarlo con `php tests/run.php`.
 * assets/form.js repite las mismas reglas en el navegador; manda siempre lo que decide esta clase.
 *
 * Forma del esquema:
 * {
 *   "steps": [ { "id", "title", "description"?, "show_if"?, "fields": [ campo, … ] } ],
 *   "summary": [ ids de campos que van en el correo de confirmación y en las columnas del listado ],
 *   "notify_fields": [ ids para el aviso al equipo; sin nombres ni contacto. Si falta, se usa "summary" ],
 *   "email_field": "email", "dni_field": "dni"
 * }
 * Campo: { "id", "type", "label", "required"?, "help"?, "options"?, "min"?, "max"?, "maxlength"?,
 *          "autocomplete"?, "show_if"?, "text"? (solo type=info) }
 * Opción: { "value", "label", "note"?, "link"?, "show_if"? }
 * Condición: { "all": [ … ] } | { "any": [ … ] } | { "field", "op": eq|neq|contains|gt|lt, "value" }
 */

final class CCF_Schema {

	const TYPES        = [ 'text', 'textarea', 'email', 'tel', 'number', 'date', 'time', 'select', 'radio', 'checkbox', 'dni', 'info' ];
	const CHOICE_TYPES = [ 'select', 'radio', 'checkbox' ];
	const OPS          = [ 'eq', 'neq', 'contains', 'gt', 'lt' ];

	/** Errores de estructura del esquema (para el panel). Vacío = esquema válido. */
	public static function check( $schema ): array {
		$errors = [];
		if ( ! is_array( $schema ) || empty( $schema['steps'] ) || ! is_array( $schema['steps'] ) ) {
			return [ 'El esquema tiene que tener "steps" con al menos una etapa.' ];
		}
		$seen = [];
		foreach ( $schema['steps'] as $i => $step ) {
			$n = $i + 1;
			if ( empty( $step['title'] ) || empty( $step['fields'] ) || ! is_array( $step['fields'] ) ) {
				$errors[] = "La etapa $n necesita \"title\" y al menos un campo en \"fields\".";
				continue;
			}
			if ( isset( $step['show_if'] ) ) {
				$errors = array_merge( $errors, self::check_cond( $step['show_if'], $seen, "etapa $n" ) );
			}
			foreach ( $step['fields'] as $field ) {
				$id = $field['id'] ?? '';
				if ( ! preg_match( '/^[a-z][a-z0-9_]{0,40}$/', (string) $id ) ) {
					$errors[] = "Etapa $n: el id \"$id\" tiene que ser en minúsculas, sin espacios ni tildes.";
					continue;
				}
				if ( isset( $seen[ $id ] ) ) {
					$errors[] = "El id \"$id\" está repetido.";
				}
				if ( ! in_array( $field['type'] ?? '', self::TYPES, true ) ) {
					$errors[] = "El campo \"$id\" tiene un tipo desconocido.";
				}
				if ( ( $field['type'] ?? '' ) !== 'info' && empty( $field['label'] ) ) {
					$errors[] = "El campo \"$id\" necesita \"label\": todo campo lleva su etiqueta visible.";
				}
				if ( in_array( $field['type'] ?? '', self::CHOICE_TYPES, true ) ) {
					if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
						$errors[] = "El campo \"$id\" necesita \"options\".";
					} else {
						foreach ( $field['options'] as $opt ) {
							if ( ! isset( $opt['value'], $opt['label'] ) ) {
								$errors[] = "Cada opción de \"$id\" necesita \"value\" y \"label\".";
							} elseif ( isset( $opt['show_if'] ) ) {
								$errors = array_merge( $errors, self::check_cond( $opt['show_if'], $seen, "opción de $id" ) );
							}
						}
					}
				}
				if ( isset( $field['show_if'] ) ) {
					$errors = array_merge( $errors, self::check_cond( $field['show_if'], $seen, "campo $id" ) );
				}
				$seen[ $id ] = true;
			}
		}
		foreach ( [ 'email_field', 'dni_field' ] as $k ) {
			if ( ! empty( $schema[ $k ] ) && ! isset( $seen[ $schema[ $k ] ] ) ) {
				$errors[] = "\"$k\" apunta a un campo que no existe.";
			}
		}
		foreach ( [ 'summary', 'notify_fields' ] as $k ) {
			foreach ( $schema[ $k ] ?? [] as $id ) {
				if ( ! isset( $seen[ $id ] ) ) {
					$errors[] = "\"$k\" incluye \"$id\", que no existe.";
				}
			}
		}
		return $errors;
	}

	/** Las condiciones solo pueden mirar campos anteriores: así se evalúan en una sola pasada. */
	private static function check_cond( $cond, array $seen, string $where ): array {
		if ( ! is_array( $cond ) ) {
			return [ "La condición de $where no es válida." ];
		}
		foreach ( [ 'all', 'any' ] as $k ) {
			if ( isset( $cond[ $k ] ) ) {
				$out = [];
				foreach ( (array) $cond[ $k ] as $c ) {
					$out = array_merge( $out, self::check_cond( $c, $seen, $where ) );
				}
				return $out;
			}
		}
		if ( ! isset( $cond['field'], $cond['op'] ) || ! in_array( $cond['op'], self::OPS, true ) ) {
			return [ "La condición de $where necesita \"field\" y un \"op\" válido (" . implode( ', ', self::OPS ) . ').' ];
		}
		if ( ! isset( $seen[ $cond['field'] ] ) ) {
			return [ "La condición de $where mira \"{$cond['field']}\", que no es un campo anterior." ];
		}
		return [];
	}

	public static function cond( $cond, array $values ): bool {
		if ( empty( $cond ) ) {
			return true;
		}
		if ( isset( $cond['all'] ) ) {
			foreach ( $cond['all'] as $c ) {
				if ( ! self::cond( $c, $values ) ) {
					return false;
				}
			}
			return true;
		}
		if ( isset( $cond['any'] ) ) {
			foreach ( $cond['any'] as $c ) {
				if ( self::cond( $c, $values ) ) {
					return true;
				}
			}
			return false;
		}
		$v      = $values[ $cond['field'] ] ?? null;
		$target = $cond['value'] ?? '';
		switch ( $cond['op'] ) {
			case 'eq':
				return is_array( $v ) ? in_array( (string) $target, $v, true ) : (string) $v === (string) $target && null !== $v;
			case 'neq':
				return is_array( $v ) ? ! in_array( (string) $target, $v, true ) : (string) $v !== (string) $target || null === $v;
			case 'contains':
				return is_array( $v ) ? in_array( (string) $target, $v, true ) : ( null !== $v && '' !== (string) $target && false !== mb_stripos( (string) $v, (string) $target ) );
			case 'gt':
				return is_numeric( $v ) && is_numeric( $target ) && $v > $target;
			case 'lt':
				return is_numeric( $v ) && is_numeric( $target ) && $v < $target;
		}
		return false;
	}

	/** Opciones que se muestran según las respuestas anteriores (ej.: sedes según el instrumento). */
	public static function visible_options( array $field, array $values ): array {
		return array_values( array_filter( $field['options'] ?? [], fn( $o ) => self::cond( $o['show_if'] ?? null, $values ) ) );
	}

	/** Todos los campos en orden, con su etapa. */
	public static function fields( array $schema ): array {
		$out = [];
		foreach ( $schema['steps'] as $i => $step ) {
			foreach ( $step['fields'] as $f ) {
				$out[ $f['id'] ] = $f + [ '_step' => $i ];
			}
		}
		return $out;
	}

	/**
	 * Evalúa un envío completo: decide qué se ve, limpia y valida.
	 * Lo oculto no se valida ni se guarda.
	 *
	 * @return array{values: array, errors: array<string,string>, steps: array<string,int>}
	 */
	public static function evaluate( array $schema, array $raw ): array {
		$values = [];
		$errors = [];
		$steps  = [];
		foreach ( $schema['steps'] as $i => $step ) {
			if ( ! self::cond( $step['show_if'] ?? null, $values ) ) {
				continue;
			}
			foreach ( $step['fields'] as $f ) {
				if ( 'info' === $f['type'] || ! self::cond( $f['show_if'] ?? null, $values ) ) {
					continue;
				}
				[ $value, $error ] = self::field_value( $f, $raw[ $f['id'] ] ?? null, $values );
				if ( $error ) {
					$errors[ $f['id'] ] = $error;
					$steps[ $f['id'] ]  = $i;
				} elseif ( null !== $value ) {
					$values[ $f['id'] ] = $value;
				}
			}
		}
		return [ 'values' => $values, 'errors' => $errors, 'steps' => $steps ];
	}

	/** @return array{0: mixed, 1: string} Valor limpio (null si está vacío) y mensaje de error. */
	private static function field_value( array $f, $raw, array $values ): array {
		$type     = $f['type'];
		$required = ! empty( $f['required'] );

		if ( 'checkbox' === $type ) {
			$raw     = is_array( $raw ) ? array_values( array_unique( array_map( 'strval', $raw ) ) ) : [];
			$allowed = array_map( fn( $o ) => (string) $o['value'], self::visible_options( $f, $values ) );
			if ( array_diff( $raw, $allowed ) ) {
				return [ null, 'Elegí entre las opciones de la lista.' ];
			}
			if ( ! $raw ) {
				return [ null, $required ? 'Marcá al menos una opción.' : '' ];
			}
			return [ $raw, '' ];
		}

		if ( is_array( $raw ) ) {
			return [ null, 'Respuesta no válida.' ];
		}
		$v = self::clean( (string) $raw, 'textarea' === $type );
		if ( '' === $v ) {
			return [ null, $required ? ( in_array( $type, self::CHOICE_TYPES, true ) ? 'Elegí una opción.' : 'Completá este campo.' ) : '' ];
		}

		$max = (int) ( $f['maxlength'] ?? ( 'textarea' === $type ? 2000 : 200 ) );
		if ( mb_strlen( $v ) > $max ) {
			return [ null, "Es demasiado largo: el máximo es de $max caracteres." ];
		}

		switch ( $type ) {
			case 'select':
			case 'radio':
				$allowed = array_map( fn( $o ) => (string) $o['value'], self::visible_options( $f, $values ) );
				return in_array( $v, $allowed, true ) ? [ $v, '' ] : [ null, 'Elegí una de las opciones.' ];
			case 'email':
				$v = mb_strtolower( $v );
				return filter_var( $v, FILTER_VALIDATE_EMAIL ) ? [ $v, '' ] : [ null, 'Escribí un correo válido, por ejemplo nombre@correo.com.' ];
			case 'dni':
				return preg_match( '/^\d{7,8}$/', $v ) ? [ $v, '' ] : [ null, 'Escribí el DNI solo con números, sin puntos (7 u 8 cifras).' ];
			case 'tel':
				$digits = preg_replace( '/\D/', '', $v );
				return preg_match( '/^[\d\s()+-]+$/', $v ) && strlen( $digits ) >= 8 && strlen( $digits ) <= 15
					? [ $v, '' ] : [ null, 'Escribí un teléfono válido, con la característica. Por ejemplo: 342 4571609.' ];
			case 'number':
				if ( ! preg_match( '/^-?\d+([.,]\d+)?$/', $v ) ) {
					return [ null, 'Escribí un número.' ];
				}
				$n = (float) str_replace( ',', '.', $v );
				if ( ( isset( $f['min'] ) && $n < $f['min'] ) || ( isset( $f['max'] ) && $n > $f['max'] ) ) {
					return [ null, 'El número tiene que estar entre ' . ( $f['min'] ?? '…' ) . ' y ' . ( $f['max'] ?? '…' ) . '.' ];
				}
				return [ $n == (int) $n ? (int) $n : $n, '' ];
			case 'date':
				$d = DateTime::createFromFormat( '!Y-m-d', $v );
				if ( ! $d || $d->format( 'Y-m-d' ) !== $v ) {
					return [ null, 'Elegí una fecha válida.' ];
				}
				if ( ( ! empty( $f['min'] ) && $v < $f['min'] ) || ( ! empty( $f['max'] ) && $v > $f['max'] ) ) {
					return [ null, 'Elegí una fecha dentro del rango disponible.' ];
				}
				return [ $v, '' ];
			case 'time':
				return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $v ) ? [ $v, '' ] : [ null, 'Elegí un horario válido.' ];
		}
		return [ $v, '' ]; // text, textarea
	}

	/** Saca etiquetas HTML y caracteres de control. */
	public static function clean( string $v, bool $multiline = false ): string {
		$v = strip_tags( $v );
		$v = $multiline ? preg_replace( '/[^\P{C}\n]/u', '', str_replace( "\r\n", "\n", $v ) ) : preg_replace( '/\p{C}/u', '', $v );
		return trim( (string) $v );
	}

	/** Texto legible de una respuesta: la etiqueta de la opción, no su valor interno. */
	public static function display( array $field, $value ): string {
		if ( null === $value || '' === $value ) {
			return '';
		}
		$labels = [];
		foreach ( $field['options'] ?? [] as $o ) {
			$labels[ (string) $o['value'] ] = $o['label'];
		}
		$one = fn( $v ) => $labels[ (string) $v ] ?? (string) $v;
		return is_array( $value ) ? implode( ', ', array_map( $one, $value ) ) : $one( $value );
	}

	/** Celda de CSV segura: evita que Excel ejecute una respuesta como fórmula. */
	public static function csv_cell( string $v ): string {
		return preg_match( '/^[=+\-@\t\r]/', $v ) ? "'" . $v : $v;
	}
}
