# Arma dist/cc-forms-<versión>.zip para subir en Plugins → Añadir nuevo → Subir plugin.
# Deja afuera las pruebas: no tienen que quedar en el servidor.
#   pwsh plugins/empaquetar.ps1
$ErrorActionPreference = 'Stop'
$src     = Join-Path $PSScriptRoot 'cc-forms'
$version = (Select-String -Path "$src/cc-forms.php" -Pattern 'Version:\s*([\d.]+)').Matches[0].Groups[1].Value
$dist    = Join-Path $PSScriptRoot '../dist'
$tmp     = Join-Path ([IO.Path]::GetTempPath()) "ccf-$version"
$zip     = Join-Path $dist "cc-forms-$version.zip"

if (Test-Path $tmp) { Remove-Item $tmp -Recurse -Force }
New-Item -ItemType Directory -Force (Join-Path $tmp 'cc-forms'), $dist | Out-Null
Get-ChildItem $src -Exclude 'tests' | Copy-Item -Destination (Join-Path $tmp 'cc-forms') -Recurse
if (Test-Path $zip) { Remove-Item $zip -Force }
Compress-Archive -Path (Join-Path $tmp 'cc-forms') -DestinationPath $zip
Remove-Item $tmp -Recurse -Force
Write-Output "Listo: $zip"
