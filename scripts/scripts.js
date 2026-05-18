document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector(".w3-table");

    // on sélectionne TOUS les th sauf le dernier (Actions)
    const headers = table.querySelectorAll("th:not(:last-child)");
    const tbody = table.querySelector("tbody");
    let currentColumn = -1;
    let isAsc = true;

    function parseDate(text) {
        const parts = text.trim().split('/');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        return new Date(0);
    }

    headers.forEach((header, columnIndex) => {
        header.addEventListener("click", () => {
            if (currentColumn === columnIndex) {
                isAsc = !isAsc;
            } else {
                isAsc = true;
                currentColumn = columnIndex;
            }

            table.querySelectorAll(".sort-arrow").forEach(el => el.textContent = "");

            // on ajoute de la flèche sur la colonne cliquée
            const arrowSpan = header.querySelector(".sort-arrow");
            if (arrowSpan) {
                arrowSpan.textContent = isAsc ? " ▲" : " ▼";
            }

            const rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.querySelector("td"));

            rows.sort((rowA, rowB) => {
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