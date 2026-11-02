import os
import re

root_dir = "."
ignore_dirs = {".git", "vendor", "node_modules", "storage", "scratch"}

files_updated = []
for dirpath, dirnames, filenames in os.walk(root_dir):
    dirnames[:] = [d for d in dirnames if d not in ignore_dirs]
    for fn in filenames:
        if fn.endswith((".php", ".blade.php", ".json", ".env", ".md")):
            fpath = os.path.join(dirpath, fn)
            try:
                with open(fpath, "r", encoding="utf-8") as f:
                    content = f.read()
            except Exception:
                continue

            new_content = re.sub(r"Banjarmasin", "Banjarbaru", content)
            new_content = re.sub(r"banjarmasin", "banjarbaru", new_content)
            new_content = re.sub(r"BANJARMASIN", "BANJARBARU", new_content)

            if new_content != content:
                with open(fpath, "w", encoding="utf-8") as f:
                    f.write(new_content)
                files_updated.append(fpath)
                print(f"Updated: {fpath}")

print(f"Total files updated: {len(files_updated)}")
