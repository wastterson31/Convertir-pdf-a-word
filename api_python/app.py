from flask import Flask, request, send_file
from pdf2docx import Converter
import os
from datetime import datetime

app = Flask(__name__)

@app.route('/convert', methods=['POST'])
def convert_pdf_to_word():
    file = request.files['file']
    
    folder_name = datetime.now().strftime('%Y%m%d%H%M%S')
    os.makedirs(folder_name, exist_ok=True)
    
    pdf_path = os.path.join(folder_name, "input.pdf")
    word_path = os.path.join(folder_name, "output.docx")
    
    file.save(pdf_path)
    
    cv = Converter(pdf_path)
    cv.convert(word_path, start=0, end=None) 
    cv.close()
    
    return send_file(word_path, as_attachment=True)

if __name__ == '__main__':
    app.run(port=5000)
