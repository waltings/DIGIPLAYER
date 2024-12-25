class GroupHierarchy {
    constructor() {
        this.init();
    }

    async init() {
        await this.loadHierarchy();
        this.initializeTreeView();
    }

    async loadHierarchy() {
        const response = await fetch('/digiplayer/public/api/groups/hierarchy');
        const data = await response.json();
        this.renderHierarchy(data.hierarchy);
    }

    renderHierarchy(hierarchy) {
        document.getElementById('groupHierarchy').innerHTML = this.buildTreeHTML(hierarchy);
    }

    buildTreeHTML(items) {
        return `
            <div class="hierarchy-tree">
                ${this.renderTreeItems(items)}
            </div>
        `;
    }

    renderTreeItems(items) {
        return items.map(item => `
            <div class="tree-item" data-id="${item.id}">
                <div class="tree-item-header">
                    <span class="tree-icon">${item.children ? '▼' : '•'}</span>
                    <span class="tree-label">${item.name}</span>
                    <span class="tree-count">(${item.device_count})</span>
                </div>
                ${item.children ? `
                    <div class="tree-children">
                        ${this.renderTreeItems(item.children)}
                    </div>
                ` : ''}
            </div>
        `).join('');
    }
}

const groupHierarchy = new GroupHierarchy();
