#!/usr/bin/env bash
# Genera el Word del informe a partir del Markdown.
#   generar.sh <Informe-Final.md> <salida.docx>
# Arma un Markdown intermedio (portada nueva + cuerpo del informe), renderiza los diagramas Mermaid
# como imagenes y aplica el filtro informe.lua. Los diagramas quedan en $SCRATCH/mermaid
# (por defecto, /tmp/generar-docx).
set -euo pipefail
AQUI="$(cd "$(dirname "$0")" && pwd)"
ENTRADA="$(realpath "$1")"; SALIDA="$(realpath -m "$2")"
export SCRATCH="${SCRATCH:-/tmp/generar-docx}"
mkdir -p "$SCRATCH/mermaid"
cp "$AQUI/pptr.json" "$SCRATCH/pptr.json"
# La plantilla de estilos se vuelve a crear siempre: cambia cuando cambia crear-reference.py.
python3 -I "$AQUI/crear-reference.py" "$SCRATCH"
python3 -I "$AQUI/preparar-md.py" "$ENTRADA" "$AQUI/portada.md" "$SCRATCH/informe.md"
cd "$AQUI/.."   # las imagenes del informe (img/...) se resuelven desde documento/
pandoc "$SCRATCH/informe.md" -f gfm+tex_math_dollars -t docx --lua-filter "$AQUI/informe.lua" \
  --reference-doc "$SCRATCH/reference.docx" --resource-path=. -o "$SALIDA"
python3 -I "$AQUI/ajustar-docx.py" "$SALIDA"   # agranda los diagramas de las paginas horizontales
