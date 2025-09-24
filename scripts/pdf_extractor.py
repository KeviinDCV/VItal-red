#!/usr/bin/env python3
import sys
import json
import PyPDF2
import re
from pathlib import Path

def extract_patient_data(pdf_path):
    """Extrae datos del paciente de un PDF"""
    try:
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            text = ""
            
            for page in pdf_reader.pages:
                text += page.extract_text()
        
        # Patrones de extracción
        patterns = {
            'numero_identificacion': r'(?:cc|cedula|identificacion)[\s:]*(\d{6,12})',
            'nombre': r'(?:nombre|paciente)[\s:]*([A-ZÁÉÍÓÚ][a-záéíóú]+(?:\s+[A-ZÁÉÍÓÚ][a-záéíóú]+)*)',
            'telefono': r'(?:tel|telefono|celular)[\s:]*(\d{7,10})',
            'fecha_nacimiento': r'(?:nacimiento|nacio)[\s:]*(\d{1,2}\/\d{1,2}\/\d{4})',
            'sexo': r'(?:sexo|genero)[\s:]*([mf]|masculino|femenino)',
            'motivo_consulta': r'(?:motivo|consulta por)[\s:]*([^.]+)',
            'diagnostico_principal': r'(?:diagnostico|dx)[\s:]*([^.]+)'
        }
        
        extracted_data = {}
        
        for field, pattern in patterns.items():
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                extracted_data[field] = match.group(1).strip()
        
        return extracted_data
        
    except Exception as e:
        return {'error': str(e)}

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({'error': 'Usage: python pdf_extractor.py <pdf_path>'}))
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    
    if not Path(pdf_path).exists():
        print(json.dumps({'error': 'File not found'}))
        sys.exit(1)
    
    result = extract_patient_data(pdf_path)
    print(json.dumps(result, ensure_ascii=False))