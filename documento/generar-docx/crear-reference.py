"""Crea reference.docx: la plantilla por defecto de pandoc con bordes en las tablas.

Uso: python3 -I crear-reference.py <carpeta de salida>
"""
import re
import subprocess
import sys
import zipfile

out_dir = sys.argv[1]
base = f"{out_dir}/reference-base.docx"
subprocess.run(["pandoc", "-o", base, "--print-default-data-file", "reference.docx"], check=True)

bordes = "<w:tblBorders>" + "".join(
    f'<w:{lado} w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
    for lado in ["top", "left", "bottom", "right", "insideH", "insideV"]
) + "</w:tblBorders>"

src = zipfile.ZipFile(base)
out = zipfile.ZipFile(f"{out_dir}/reference.docx", "w", zipfile.ZIP_DEFLATED)
for item in src.infolist():
    data = src.read(item.filename)
    if item.filename == "word/document.xml":
        # Sección del cuerpo (desde «1. Perfil»): carta, márgenes, pie con el número de página y numeración desde 1.
        xml = data.decode()
        pagina = (
            '<w:footerReference w:type="default" r:id="rIdPie1"/>'
            '<w:pgSz w:w="12240" w:h="15840"/>'
            '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>'
        )  # el reinicio de la numeración en 1 lo pone informe.lua en la primera sección del cuerpo
        xml = re.sub(r"(<w:sectPr[^>]*>)", lambda m: m.group(1) + pagina, xml, count=1)
        if 'xmlns:r=' not in xml.split(">", 2)[1] and 'xmlns:r=' not in xml[:2000]:
            xml = xml.replace("<w:document ", '<w:document xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" ', 1)
        data = xml.encode()
    elif item.filename == "word/_rels/document.xml.rels":
        xml = data.decode().replace(
            "</Relationships>",
            '<Relationship Id="rIdPie1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/></Relationships>')
        data = xml.encode()
    elif item.filename == "[Content_Types].xml":
        xml = data.decode().replace(
            "</Types>",
            '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/></Types>')
        data = xml.encode()
    if item.filename == "word/styles.xml":
        xml = data.decode()
        estilo = re.search(r'<w:style w:type="table"[^>]*w:styleId="Table".*?</w:style>', xml, re.S).group(0)
        nuevo = (estilo.replace("<w:tblPr>", "<w:tblPr>" + bordes, 1) if "<w:tblPr>" in estilo
                 else estilo.replace("</w:style>", f"<w:tblPr>{bordes}</w:tblPr></w:style>"))
        xml = xml.replace(estilo, nuevo)
        # Portada: títulos en negrita de 16 pt, subtítulos en negrita de 12 pt (como el Word de referencia),
        # párrafos centrados (Docente, Lugar y fecha, logo, QR) y datos del grupo de 11 pt.
        def estilo(id_):
            return re.search(r'<w:style w:type="paragraph" w:styleId="%s".*?</w:style>' % id_, xml, re.S).group(0)
        xml = xml.replace(estilo("Title"),
            '<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/>'
            '<w:next w:val="BodyText"/><w:uiPriority w:val="10"/><w:qFormat/>'
            '<w:pPr><w:spacing w:before="0" w:after="120" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr>'
            '<w:rPr><w:b/><w:bCs/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr></w:style>')
        xml = xml.replace(estilo("Subtitle"),
            '<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/>'
            '<w:next w:val="BodyText"/><w:uiPriority w:val="11"/><w:qFormat/>'
            '<w:pPr><w:spacing w:before="0" w:after="120" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr>'
            '<w:rPr><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:style>')
        portada = ('<w:style w:type="paragraph" w:customStyle="1" w:styleId="PortadaCentro">'
                   '<w:name w:val="Portada Centro"/><w:basedOn w:val="Normal"/><w:qFormat/>'
                   '<w:pPr><w:spacing w:before="0" w:after="120"/><w:jc w:val="center"/></w:pPr></w:style>'
                   '<w:style w:type="paragraph" w:customStyle="1" w:styleId="PortadaDato">'
                   '<w:name w:val="Portada Dato"/><w:basedOn w:val="Normal"/><w:qFormat/>'
                   '<w:pPr><w:spacing w:before="0" w:after="60"/></w:pPr>'
                   '<w:rPr><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr></w:style>')
        data = xml.replace("</w:styles>", portada + "</w:styles>").encode()
    out.writestr(item, data)
pie = ('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
       '<w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
       'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
       '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
       '<w:r><w:fldChar w:fldCharType="begin"/></w:r>'
       '<w:r><w:instrText xml:space="preserve"> PAGE </w:instrText></w:r>'
       '<w:r><w:fldChar w:fldCharType="separate"/></w:r>'
       '<w:r><w:t>1</w:t></w:r>'
       '<w:r><w:fldChar w:fldCharType="end"/></w:r></w:p></w:ftr>')
out.writestr("word/footer1.xml", pie)
out.close()
