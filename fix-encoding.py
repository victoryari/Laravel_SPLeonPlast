import os

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with open(path, 'rb') as f:
    data = f.read()

text = data.decode('utf-8')
# Try to fix double encoding
try:
    fixed_text = text.encode('windows-1252').decode('utf-8')
    with open(path, 'w', encoding='utf-8') as f:
        f.write(fixed_text)
    print("Fixed encoding.")
except Exception as e:
    print("Error:", e)
