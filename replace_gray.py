import os
import re
import codecs

def replace_in_file(filepath):
    try:
        with codecs.open(filepath, 'r', 'utf-8') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {filepath}: {e}")
        return False
        
    # Replace gray with slate for standard tailwind prefixes
    # We match prefixes: bg-, text-, border-, ring-, outline-, shadow-, divide-
    # followed by gray- and a number
    
    # We want to replace -gray- with -slate-
    new_content = re.sub(r'\b(bg|text|border|ring|outline|shadow|divide|from|to|via)-gray-([0-9]{2,3})\b', r'\1-slate-\2', content)
    # Also catch hover:, focus:, etc.
    new_content = re.sub(r'([a-z]+:)?(bg|text|border|ring|outline|shadow|divide|from|to|via)-gray-([0-9]{2,3})\b', r'\1\2-slate-\3', new_content)

    if new_content != content:
        try:
            with codecs.open(filepath, 'w', 'utf-8') as f:
                f.write(new_content)
            return True
        except Exception as e:
            print(f"Error writing {filepath}: {e}")
            
    return False

def process_directory(directory):
    changed_files = 0
    total_files = 0
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.blade.php'):
                total_files += 1
                filepath = os.path.join(root, file)
                if replace_in_file(filepath):
                    changed_files += 1
                    # print(f"Updated: {filepath}")
                    
    print(f"Processed {total_files} files.")
    print(f"Updated {changed_files} files with slate colors.")

if __name__ == "__main__":
    views_dir = r"C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views"
    process_directory(views_dir)
