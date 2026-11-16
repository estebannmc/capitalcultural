<?php
/**
 * Pruebas del esquema, sin WordPress.
 * Correr con Docker:  docker run --rm -v "$PWD":/app -w /app php:8.1-cli php tests/run.php
 * (desde plugins/cc-forms). Termina con código 1 si algo falla.
 */

require __DIR__ . '/../includes/class-ccf-schema.php';

$fails = 0;
function ok( bool $cond, string $name ): void {
	global $fails;
	echo ( $cond ? '  ok   ' : '  FALLA ' ) . $name . "\n";
	$fails += $cond ? 0 : 1;
}

$schema = [
	'steps' => [
		[ 'id' => 'a', 'title' => 'Alumno', 'fields' => [
			[ 'id' => 'nombre', 'type' => 'text', 'label' => 'Nombre', 'required' => true ],
			[ 'id' => 'dni', 'type' => 'dni', 'label' => 'DNI', 'required' => true ],
			[ 'id' => 'edad', 'type' => 'number', 'label' => 'Edad', 'required' => true, 'min' => 7, 'max' => 14 ],
			[ 'id' => 'email', 'type' => 'email', 'label' => 'Correo', 'required' => true ],
		] ],
		[ 'id' => 'b', 'title' => 'Sede', 'fields' => [
			[ 'id' => 'tipo', 'type' => 'radio', 'label' => 'Tipo', 'required' => true, 'options' => [
				[ 'value' => 'cuerdas', 'label' => 'Cuerdas' ],
				[ 'value' => 'viento', 'label' => 'Viento y percusión' ],
			] ],
			[ 'id' => 'sede', 'type' => 'select', 'label' => 'Sede', 'required' => true, 'options' => [
				[ 'value' => 'e38', 'label' => 'Escuela 38', 'show_if' => [ 'field' => 'tipo', 'op' => 'eq', 'value' => 'cuerdas' ] ],
				[ 'value' => 'cumbia', 'label' => 'La Cumbia', 'show_if' => [ 'field' => 'tipo', 'op' => 'eq', 'value' => 'viento' ] ],
				[ 'value' => 'e1225', 'label' => 'Escuela 1225', 'show_if' => [ 'field' => 'tipo', 'op' => 'eq', 'value' => 'viento' ] ],
			] ],
			[ 'id' => 'exalumno', 'type' => 'radio', 'label' => '¿Es alumno?', 'required' => true,
				'show_if' => [ 'field' => 'sede', 'op' => 'eq', 'value' => 'e1225' ],
				'options' => [ [ 'value' => 'si', 'label' => 'Sí' ], [ 'value' => 'no', 'label' => 'No' ] ] ],
		] ],
		[ 'id' => 'c', 'title' => 'Adulto', 'show_if' => [ 'field' => 'edad', 'op' => 'lt', 'value' => 18 ], 'fields' => [
			[ 'id' => 'adulto', 'type' => 'text', 'label' => 'Adulto', 'required' => true ],
			[ 'id' => 'redes', 'type' => 'checkbox', 'label' => 'Redes', 'options' => [
				[ 'value' => 'ig', 'label' => 'Instagram' ], [ 'value' => 'yt', 'label' => 'YouTube' ] ] ],
		] ],
	],
	'summary'     => [ 'nombre', 'sede' ],
	'email_field' => 'email',
	'dni_field'   => 'dni',
];

$valid = [ 'nombre' => ' Gómez, Martina ', 'dni' => '55123456', 'edad' => '9', 'email' => 'Ana@Correo.com',
	'tipo' => 'viento', 'sede' => 'e1225', 'exalumno' => 'si', 'adulto' => 'Laura', 'redes' => [ 'ig' ] ];

echo "Estructura\n";
ok( [] === CCF_Schema::check( $schema ), 'el esquema de prueba es válido' );
$bad = $schema;
$bad['steps'][0]['fields'][0]['show_if'] = [ 'field' => 'sede', 'op' => 'eq', 'value' => 'x' ];
ok( (bool) CCF_Schema::check( $bad ), 'rechaza una condición que mira un campo posterior' );
$bad = $schema;
$bad['steps'][1]['fields'][0]['id'] = 'nombre';
ok( (bool) CCF_Schema::check( $bad ), 'rechaza ids repetidos' );
foreach ( glob( __DIR__ . '/../forms/*.json' ) as $file ) {
	$errs = CCF_Schema::check( json_decode( file_get_contents( $file ), true )['schema'] ?? null );
	ok( [] === $errs, 'la plantilla ' . basename( $file ) . ' es válida' . ( $errs ? ': ' . implode( ' ', $errs ) : '' ) );
}

echo "Envío válido\n";
$r = CCF_Schema::evaluate( $schema, $valid );
ok( [] === $r['errors'], 'sin errores' );
ok( 'Gómez, Martina' === $r['values']['nombre'], 'recorta espacios' );
ok( 'ana@correo.com' === $r['values']['email'], 'pasa el correo a minúsculas' );
ok( 9 === $r['values']['edad'], 'convierte la edad a número' );
ok( 'si' === $r['values']['exalumno'], 'guarda la pregunta condicional visible' );

echo "Condicionales\n";
$r = CCF_Schema::evaluate( $schema, [ 'tipo' => 'cuerdas', 'sede' => 'e1225', 'exalumno' => 'si' ] + $valid );
ok( isset( $r['errors']['sede'] ), 'rechaza una sede que no corresponde al tipo de instrumento' );
ok( ! isset( $r['values']['exalumno'] ), 'no guarda la respuesta de una pregunta oculta' );
$r = CCF_Schema::evaluate( $schema, [ 'sede' => 'cumbia', 'exalumno' => '' ] + $valid );
ok( ! isset( $r['errors']['exalumno'] ), 'una pregunta oculta no es obligatoria' );
$r = CCF_Schema::evaluate( $schema, [ 'edad' => '9', 'adulto' => '' ] + $valid );
ok( 2 === ( $r['steps']['adulto'] ?? null ), 'informa en qué etapa está el error' );
$adult_schema = $schema;
$adult_schema['steps'][0]['fields'][2]['max'] = 99;
$r = CCF_Schema::evaluate( $adult_schema, [ 'edad' => '30', 'adulto' => '' ] + $valid );
ok( ! isset( $r['errors']['adulto'] ) && ! isset( $r['values']['adulto'] ), 'una etapa oculta se saltea entera' );
ok( CCF_Schema::cond( [ 'any' => [ [ 'field' => 'x', 'op' => 'eq', 'value' => 1 ], [ 'field' => 'y', 'op' => 'gt', 'value' => 3 ] ] ], [ 'y' => 5 ] ), 'combina condiciones con O' );
ok( ! CCF_Schema::cond( [ 'all' => [ [ 'field' => 'x', 'op' => 'eq', 'value' => 1 ], [ 'field' => 'y', 'op' => 'gt', 'value' => 3 ] ] ], [ 'y' => 5 ] ), 'combina condiciones con Y' );
ok( CCF_Schema::cond( [ 'field' => 'redes', 'op' => 'contains', 'value' => 'yt' ], [ 'redes' => [ 'ig', 'yt' ] ] ), '"contiene" funciona con casillas' );
ok( CCF_Schema::cond( [ 'field' => 'nada', 'op' => 'neq', 'value' => 'x' ], [] ), '"es distinto de" es verdadero si no hay respuesta' );

echo "Validación\n";
$cases = [
	[ 'dni', '28.456.789', 'DNI con puntos' ],
	[ 'dni', '123', 'DNI corto' ],
	[ 'email', 'ana@', 'correo incompleto' ],
	[ 'edad', '6', 'edad fuera de rango' ],
	[ 'edad', 'nueve', 'edad que no es número' ],
	[ 'nombre', '', 'campo obligatorio vacío' ],
	[ 'nombre', str_repeat( 'a', 201 ), 'texto demasiado largo' ],
	[ 'redes', [ 'tiktok' ], 'casilla que no está en la lista' ],
	[ 'nombre', [ 'a' ], 'lista donde va texto' ],
];
foreach ( $cases as [ $field, $value, $name ] ) {
	$r = CCF_Schema::evaluate( $schema, [ $field => $value ] + $valid );
	ok( isset( $r['errors'][ $field ] ), "rechaza $name" );
}
$r = CCF_Schema::evaluate( $schema, [ 'nombre' => '<b>Ana</b><script>x</script>' ] + $valid );
ok( 'Anax' === $r['values']['nombre'], 'saca las etiquetas HTML' );

echo "Exportación\n";
ok( "'=HYPERLINK(1)" === CCF_Schema::csv_cell( '=HYPERLINK(1)' ), 'neutraliza fórmulas en el CSV' );
ok( 'Ana' === CCF_Schema::csv_cell( 'Ana' ), 'deja el texto común igual' );
ok( 'Viento y percusión' === CCF_Schema::display( $schema['steps'][1]['fields'][0], 'viento' ), 'muestra la etiqueta de la opción' );
ok( 'Instagram, YouTube' === CCF_Schema::display( $schema['steps'][2]['fields'][1], [ 'ig', 'yt' ] ), 'une las casillas marcadas' );

echo $fails ? "\n$fails pruebas fallaron.\n" : "\nTodas las pruebas pasaron.\n";
exit( $fails ? 1 : 0 );
