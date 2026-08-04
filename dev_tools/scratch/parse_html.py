import re
import json

def parse_html_to_json(html_path):
    with open(html_path, 'r', encoding='utf-8') as f:
        html = f.read()

    # accordion items
    # pattern for accordion items
    accordion_pattern = re.compile(
        r'<div class="accordion-item">(.*?)</div>\s*</div>\s*</div>\s*</div>', 
        re.DOTALL
    )
    
    # We can also parse accordion by matching headings and contents.
    # Let's extract heading, items, and modals for each collapse.
    
    # Alternative: find all accordion-item elements
    # Let's write a robust parser
    import bs4
    soup = bs4.BeautifulSoup(html, 'html.parser')
    
    accordion = soup.find('div', id='accordion')
    if not accordion:
        print("Accordion not found")
        return
        
    categories = []
    
    items = accordion.find_all('div', class_='accordion-item', recursive=False)
    for idx, item in enumerate(items):
        title_el = item.find('span', class_='accordion-title')
        cat_title = title_el.get_text(strip=True) if title_el else f"Category {idx}"
        
        # heading id
        heading = item.find('h2', class_='accordion-header')
        heading_id = heading.get('id', f'heading{idx}') if heading else f'heading{idx}'
        
        collapse = item.find('div', class_='accordion-collapse')
        collapse_id = collapse.get('id', f'collapse{idx}') if collapse else f'collapse{idx}'
        
        items_div = item.find('div', id=lambda x: x and x.startswith('items'))
        items_div_id = items_div.get('id', f'items{idx}') if items_div else f'items{idx}'
        
        category_data = {
            "categoryId": idx,
            "headingId": heading_id,
            "collapseId": collapse_id,
            "itemsDivId": items_div_id,
            "categoryTitle": cat_title,
            "checkCountId": f"check-count{collapse_id.replace('collapse', '')}",
            "items": []
        }
        
        # Find all form-check within this item
        form_checks = items_div.find_all('div', class_='form-check') if items_div else []
        for fc in form_checks:
            input_el = fc.find('input', class_='form-check-input')
            chk_id = input_el.get('id') if input_el else ""
            
            link_el = fc.find('a')
            target_modal_id = link_el.get('data-bs-target', '').replace('#', '') if link_el else ""
            item_label_html = link_el.decode_contents() if link_el else ""
            link_id = link_el.get('id', '') if link_el else ""
            
            # Find the corresponding modal
            modal_el = items_div.find('div', id=target_modal_id)
            modal_html = ""
            if modal_el:
                # Get modal-dialog inner html or modal-content html
                modal_content = modal_el.find('div', class_='modal-content')
                if modal_content:
                    modal_html = modal_content.decode_contents()
            
            category_data["items"].append({
                "id": chk_id,
                "linkId": link_id,
                "targetModalId": target_modal_id,
                "labelHtml": item_label_html,
                "modalContentHtml": modal_html
            })
            
        categories.append(category_data)
        
    with open('c:/xampp/htdocs/task/data/checklist.json', 'w', encoding='utf-8') as f:
        json.dump(categories, f, ensure_ascii=False, indent=2)
        
    print(f"Successfully extracted {len(categories)} categories to checklist.json")

if __name__ == '__main__':
    parse_html_to_json('c:/xampp/htdocs/task/index.html')
