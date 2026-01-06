/**
 * CMS連動チェックリスト動的生成
 * APIから取得したデータでアコーディオンとモーダルを生成
 */

(function() {
    'use strict';
    
    // APIからデータを取得
    function loadChecklistData() {
        return fetch('/task/api/get-content.php')
            .then(response => {
                if (!response.ok) throw new Error('API error');
                return response.json();
            })
            .then(data => {
                if (data.accordions && Array.isArray(data.accordions)) {
                    return data;
                }
                throw new Error('Invalid data structure');
            })
            .catch(err => {
                console.warn('CMS checklist data load failed:', err);
                return null;
            });
    }
    
    // アコーディオンHTMLを生成
    function generateAccordionHTML(accordion, index) {
        const accordionId = `accordion_${index}`;
        const collapseId = `collapse${index}`;
        const headingId = `heading${index}`;
        
        let itemsHTML = '';
        accordion.items.forEach((item, itemIndex) => {
            const itemId = `Check${index}-${itemIndex}`;
            const modalId = `Modal${index}-${itemIndex}`;
            const linkId = `M${index}-${itemIndex}`;
            
            // チェック項目HTML
            if (item.hasCheckbox) {
                itemsHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="${itemId}" name="${itemId}">
                        <label class="form-check-label" for="${itemId}">
                `;
            } else {
                itemsHTML += `<div class="form-check">`;
            }
            
            if (item.hasModal) {
                itemsHTML += `
                    <a href="#${modalId}" class="link-primary" data-bs-toggle="modal" data-bs-target="#${modalId}" id="${linkId}">
                        ${escapeHtml(item.title)}
                    </a>
                `;
            } else {
                itemsHTML += escapeHtml(item.title);
            }
            
            if (item.hasCheckbox) {
                itemsHTML += `</label>`;
            }
            
            itemsHTML += `</div>`;
            
            // モーダルHTML
            if (item.hasModal) {
                itemsHTML += generateModalHTML(item, modalId, index, itemIndex);
            }
        });
        
        return `
            <div class="accordion-item">
                <h2 class="accordion-header" id="${headingId}">
                    <div class="progress">
                        <div class="progress-bar${index}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <button class="accordion-button collapsed shadow text-reset fw-bold" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                            aria-expanded="false" aria-controls="${collapseId}">
                        ${escapeHtml(accordion.title)}
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#accordion">
                    <div class="accordion-body">
                        <div id="items${index}">
                            ${itemsHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // モーダルHTMLを生成
    function generateModalHTML(item, modalId, accordionIndex, itemIndex) {
        const modalLabelId = `ModalLabel${accordionIndex}-${itemIndex}`;
        let contentHTML = '';
        
        if (item.modalContent) {
            const content = item.modalContent;
            
            switch (content.type) {
                case 'html':
                    contentHTML = content.content;
                    break;
                    
                case 'list':
                    try {
                        const items = typeof content.items === 'string' ? JSON.parse(content.items) : content.items;
                        contentHTML = '<ul>';
                        items.forEach(listItem => {
                            contentHTML += `<li>${listItem}</li>`;
                        });
                        contentHTML += '</ul>';
                    } catch (e) {
                        contentHTML = content.content || '';
                    }
                    break;
                    
                case 'structured':
                    try {
                        const sections = typeof content.sections === 'string' ? JSON.parse(content.sections) : content.sections;
                        sections.forEach(section => {
                            contentHTML += `<div><h5 class="kokuban sticky-top">${escapeHtml(section.title)}</h5>`;
                            if (section.items && section.items.length > 0) {
                                contentHTML += '<ul>';
                                section.items.forEach(listItem => {
                                    contentHTML += `<li>${listItem}</li>`;
                                });
                                contentHTML += '</ul>';
                            }
                            if (section.image) {
                                contentHTML += `<img class="okzoom" src="${escapeHtml(section.image)}">`;
                            }
                            contentHTML += '</div>';
                        });
                    } catch (e) {
                        contentHTML = content.content || '';
                    }
                    break;
                    
                default:
                    contentHTML = content.content || '';
            }
        }
        
        return `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalLabelId}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title kokuban" id="${modalLabelId}">${escapeHtml(item.modalTitle)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body d-inline-block text-wrap">
                            ${contentHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // HTMLエスケープ
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // アコーディオンコンテナを生成
    function renderChecklists(data) {
        const accordionContainer = document.getElementById('accordion');
        if (!accordionContainer) {
            console.warn('Accordion container not found');
            return;
        }
        
        // 既存のアコーディオンを削除（スローガン部分は保持）
        const existingItems = accordionContainer.querySelectorAll('.accordion-item');
        existingItems.forEach(item => item.remove());
        
        // ソートしてから生成
        const sortedAccordions = [...data.accordions].sort((a, b) => (a.order || 0) - (b.order || 0));
        
        sortedAccordions.forEach((accordion, index) => {
            const html = generateAccordionHTML(accordion, index);
            accordionContainer.insertAdjacentHTML('beforeend', html);
        });
        
        // 進捗バー機能を初期化
        if (typeof initProgressBars === 'function') {
            initProgressBars();
        }
        
        console.log('CMS checklist rendered:', sortedAccordions.length, 'accordions');
    }
    
    // 初期化
    function init() {
        loadChecklistData().then(data => {
            if (data && data.accordions) {
                renderChecklists(data);
                
                // グローバルに保存
                window.cmsChecklistData = data;
                window.cmsChecklistLoaded = true;
            }
        });
    }
    
    // DOMContentLoaded後に実行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
