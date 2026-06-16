function changerCouleurSelect(selectElement) {
    if (!selectElement) return;
    const optionSelectionnee = selectElement.options[selectElement.selectedIndex];

    selectElement.classList.remove('etat-ok', 'etat-reserve', 'etat-reparation', 'etat-endommage', 'etat-disparu');

    if (optionSelectionnee && optionSelectionnee.className) {
        selectElement.classList.add(optionSelectionnee.className);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    function parseDate(text) {
        const parts = text.trim().split('/');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        return new Date(0);
    }

    document.querySelectorAll(".w3-table").forEach((table) => {
        const tbody = table.tBodies[0];
        const headers = table.tHead ? table.tHead.querySelectorAll("th") : [];
        if (!tbody || !headers.length || !table.querySelector(".sort-arrow")) return;

        let currentColumn = -1;
        let isAsc = true;

        headers.forEach((header, columnIndex) => {
            if (header.textContent.trim() === "Actions") {
                return;
            }

            header.style.cursor = "pointer";

            header.addEventListener("click", () => {
                if (currentColumn === columnIndex) {
                    isAsc = !isAsc;
                } else {
                    isAsc = true;
                    currentColumn = columnIndex;
                }

                table.querySelectorAll(".sort-arrow").forEach(el => el.textContent = "");

                const arrowSpan = header.querySelector(".sort-arrow");
                if (arrowSpan) {
                    arrowSpan.textContent = isAsc ? " ▲" : " ▼";
                }

                const rows = Array.from(tbody.rows);

                rows.sort((rowA, rowB) => {
                    const aRenduAttr = rowA.getAttribute('data-rendu');
                    const bRenduAttr = rowB.getAttribute('data-rendu');
                    if (aRenduAttr !== null && bRenduAttr !== null) {
                        const aRendu = aRenduAttr === '1' ? 1 : 0;
                        const bRendu = bRenduAttr === '1' ? 1 : 0;
                        if (aRendu !== bRendu) {
                            return aRendu - bRendu; // Non-rendu (0) avant rendu (1)
                        }
                    }

                    if (!rowA.children[columnIndex] || !rowB.children[columnIndex]) return 0;

                    let cellA = rowA.children[columnIndex].textContent.trim();
                    let cellB = rowB.children[columnIndex].textContent.trim();

                    if (cellA.includes('/') || cellB.includes('/')) {
                        return isAsc ? parseDate(cellA) - parseDate(cellB) : parseDate(cellB) - parseDate(cellA);
                    }

                    return isAsc ?
                        cellA.localeCompare(cellB, undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        }) :
                        cellB.localeCompare(cellA, undefined, {
                            numeric: true,
                            sensitivity: 'base'
                        });
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });

    document.querySelectorAll("select[name='etat'], select[name='etat_restitution']").forEach(select => {
        changerCouleurSelect(select);
    });
});
