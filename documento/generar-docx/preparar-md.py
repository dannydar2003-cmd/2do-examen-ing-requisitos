"""Arma el Markdown que se convierte a Word: portada nueva + el cuerpo del informe.

Uso: python3 -I preparar-md.py <Informe-Final.md> <portada.md> <salida.md>

- Descarta la portada en tabla y la tabla de contenido escritas a mano (el Word lleva su propio índice).
- Sube un nivel los títulos (## 1. Perfil pasa a ser un título de nivel 1), fuera de los bloques de código.
"""
import re
import sys

informe, portada, salida = sys.argv[1:4]
texto = open(informe, encoding="utf-8").read()

inicio = re.search(r"^## 1\. Perfil$", texto, re.M)
if not inicio:
    sys.exit("No se encontró la sección '## 1. Perfil' en el informe.")
cuerpo = texto[inicio.start():]

lineas, en_codigo = [], False
for linea in cuerpo.split("\n"):
    if linea.startswith("```"):
        en_codigo = not en_codigo
    elif not en_codigo and re.match(r"^#{2,6} ", linea):
        linea = linea[1:]
    lineas.append(linea)

with open(salida, "w", encoding="utf-8") as f:
    f.write(open(portada, encoding="utf-8").read().rstrip() + "\n\n" + "\n".join(lineas))
