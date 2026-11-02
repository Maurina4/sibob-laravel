from PIL import Image

im = Image.open(r'c:\Users\USER\Downloads\sibob-laravel\scratch\preview_screenshot.png')
print('Screenshot size:', im.size)

# Crop the paper header area
# Find where the white paper is
import numpy as np
arr = np.array(im.convert('RGB'))

# Find the white page box (paper container background is dark #1e293b, page is white #ffffff)
# Look for rows where center is white
paper_mask = (arr[:, 600, 0] > 250) & (arr[:, 600, 1] > 250) & (arr[:, 600, 2] > 250)
white_rows = np.where(paper_mask)[0]

if len(white_rows) > 0:
    top_y = white_rows[0]
    print('Paper top Y:', top_y)
    # Check paper left and right at top_y + 20
    row = arr[top_y + 20, :, :]
    is_white = (row[:, 0] > 240) & (row[:, 1] > 240) & (row[:, 2] > 240)
    white_cols = np.where(is_white)[0]
    left_x = white_cols[0]
    right_x = white_cols[-1]
    print(f'Paper bounds: X=[{left_x}, {right_x}], Y=[{top_y}, {top_y + 300}]')
    
    # Crop the kop header of the paper
    kop = im.crop((left_x, top_y, right_x, top_y + 160))
    kop.save(r'C:\Users\USER\.gemini\antigravity\brain\22c807d7-ca36-425a-9b0c-a2eb09b87cbe\scratch\current_rendered_kop.png')
    print('Saved current_rendered_kop.png')
else:
    print('Could not find white paper')
