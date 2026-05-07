<div class="margin w3-border w3-padding" style="background: white;">
  <?php getTables($db); ?>
  <div id="tooltip" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; z-index:1000;"></div>
</div>

<script>
  //Scripts to enable the db manipulations to work
  //Create a new row in the table to prepare for an insert
  function newValue(tableName, metadata) {
    if ($('#new-input-row-' + tableName).length) {
      temporaryAlert('warning', tableName, 'Il existe déjà une insertion en attente', 10);
    } else {

      $.ajax({
        url: 'controllers/database/tables/prepareCreateValue.php',
        type: 'GET',
        data: {
          tableName: tableName,
          columnMetadata: metadata
        },
        success: function(response) {
          $('#table-values-' + tableName).prepend(response);
          adjustSpacer();
          updateInputWidth(tableName);
        },
        error: function(response) {
          temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
        },
      });
    }
  }

  //Applies an input to each cell of a chosen row in the table to prepare for a modification
  function modifyValue(tableName, metadata, nbRow, values) {
    $.ajax({
      url: 'controllers/database/tables/prepareModifyValue.php',
      type: 'GET',
      data: {
        tableName: tableName,
        row: nbRow,
        values: values,
        columnMetadata: metadata
      },
      success: function(response) {

        $('#tr-' + tableName + '-' + nbRow).html(response);
        adjustSpacer();
        updateInputWidth(tableName);
        filterTable(tableName);

      },
      error: function(response) {
        temporaryAlert('error', tableName, response.responseText, 10);
      },
    });
  }

  //Save the new values or modified values of a table if confirmed, or discards them if not
  function saveValue(tableName, metadata, accept, isNewValue, nbRow = 0, values = {}) {
    if (accept) {

      var newValues = {};
      if (isNewValue) {

        columnNames = metadata;
        columnNames.forEach((column) => {
          newValues[column.name] = ($('#new-input-row-' + tableName + ' #input-' + column.name).val());
        });

        $.ajax({
          url: 'controllers/database/tables/verifyValue.php',
          type: 'GET',
          data: {
            tableName: tableName,
            values: newValues,
            columnMetadata: metadata,
          },
          success: function(response) {
            response = JSON.parse(response)
            if (response.error != undefined) {
              temporaryAlert('error', tableName, response.error, 10);
            } else if (response.warning != undefined) {
              temporaryAlert('warning', tableName, response.warning, 10);
            } else if (response.info != undefined) {
              temporaryAlert('info', tableName, response.info, 10);
            } else {

              $.ajax({
                url: 'controllers/database/tables/createValue.php',
                type: 'POST',
                data: {
                  tableName: tableName,
                  values: response.success,
                  columnMetadata: metadata,
                },
                success: function(response) {
                  response = JSON.parse(response)
                  if (response.error != undefined) {
                    temporaryAlert('error', tableName, response.error, 10);
                  } else if (response.warning != undefined) {
                    temporaryAlert('warning', tableName, response.warning, 10);
                  } else if (response.info != undefined) {
                    temporaryAlert('info', tableName, response.info, 10);
                  } else {
                    temporaryAlert('success', tableName, response.success, 10);
                    $('#new-input-row-' + tableName).remove();
                    refreshValues(tableName, metadata);
                    filterTable(tableName);
                  }
                },
                error: function(response) {
                  temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
                },
              });

            }
          },
          error: function(response) {
            temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
          },
        });

      } else {
        columnNames = metadata;
        columnNames.forEach((column) => {
          newValues[column.name] = ($('#tr-' + tableName + '-' + nbRow + ' #input-' + column.name).val());
        });

        $.ajax({
          url: 'controllers/database/tables/verifyValue.php',
          type: 'GET',
          data: {
            tableName: tableName,
            values: newValues,
            columnMetadata: metadata,
            modify: values,
          },
          success: function(response) {
            response = JSON.parse(response)
            if (response.error != undefined) {
              temporaryAlert('error', tableName, response.error, 10);
            } else if (response.warning != undefined) {
              temporaryAlert('warning', tableName, response.warning, 10);
            } else if (response.info != undefined) {
              temporaryAlert('info', tableName, response.info, 10);
            } else {
              var finalValues = response.success;

              $.ajax({
                url: 'controllers/database/tables/modifyValue.php',
                type: 'POST',
                data: {
                  tableName: tableName,
                  values: finalValues,
                  oldValues: values,
                  columnMetadata: metadata,
                },
                success: function(response) {
                  response = JSON.parse(response)
                  if (response.error != undefined) {
                    temporaryAlert('error', tableName, response.error, 10);
                  } else if (response.warning != undefined) {
                    temporaryAlert('warning', tableName, response.warning, 10);
                  } else if (response.info != undefined) {
                    temporaryAlert('info', tableName, response.info, 10);
                  } else {
                    temporaryAlert('success', tableName, response.success, 10);
                    $.ajax({
                      url: 'controllers/database/tables/setValue.php',
                      type: 'POST',
                      data: {
                        tableName: tableName,
                        row: nbRow,
                        values: finalValues,
                        columnMetadata: metadata,
                        listFK: <?= json_encode($listFK) ?>
                      },
                      success: function(response) {
                        $('#tr-' + tableName + '-' + nbRow).html(response);
                        adjustSpacer();
                        updateInputWidth(tableName);
                        filterTable(tableName);
                      },
                      error: function(response) {
                        temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
                      },
                    });

                  }
                },
                error: function(response) {
                  temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
                },
              });

            }
          },
          error: function(response) {
            temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
          },
        });

      }
    } else {
      if (isNewValue) {

        $('#new-input-row-' + tableName).remove();
        temporaryAlert('info', tableName, 'Annulation de l\'insertion', 10);

      } else {

        $.ajax({
          url: 'controllers/database/tables/setValue.php',
          type: 'GET',
          data: {
            tableName: tableName,
            row: nbRow,
            values: values,
            columnMetadata: metadata,
          },
          success: function(response) {
            $('#tr-' + tableName + '-' + nbRow).html(response);
            adjustSpacer();
            updateInputWidth(tableName);
            filterTable(tableName);
          },
          error: function(response) {
            temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
          },
        });

      }
    }
  }

  //Delete a row from the table after confirmation
  function deleteValue(tableName, metadata, listFK, nbRow, values) {
    $('#tr-' + tableName + '-' + nbRow).css({
      backgroundColor: 'red'
    });
    setTimeout(function() {

      const choice = confirm('Voulez vous supprimer la ligne ' + nbRow);
      if (choice) {

        $.ajax({
          url: 'controllers/database/tables/deleteValue.php',
          type: 'POST',
          data: {
            tableName: tableName,
            values: values,
            columnMetadata: metadata,
            listFK: listFK
          },
          success: function(response) {
            response = JSON.parse(response)
            if (response.error != undefined) {
              $('#tr-' + tableName + '-' + nbRow).css({
                backgroundColor: 'white'
              });
              temporaryAlert('error', tableName, response.error, 10);
            } else if (response.warning != undefined) {
              $('#tr-' + tableName + '-' + nbRow).css({
                backgroundColor: 'white'
              });
              temporaryAlert('warning', tableName, response.warning, 10);
            } else if (response.info != undefined) {
              $('#tr-' + tableName + '-' + nbRow).css({
                backgroundColor: 'white'
              });
              temporaryAlert('info', tableName, response.info, 10);
            } else {
              temporaryAlert('success', tableName, 'Ligne supprimée avec succés', 6);
              refreshValues(tableName, metadata);
              filterTable(tableName);
            }
          },
          error: function(response) {
            $('#tr-' + tableName + '-' + nbRow).css({
              backgroundColor: 'white'
            });
            temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
          },
        });

      } else {
        $('#tr-' + tableName + '-' + nbRow).css({
          backgroundColor: 'white'
        });
        temporaryAlert('info', tableName, 'Suppression annulée', 6);
      }
    }, 1);
  }

  //Refresh the values of a chosen table
  function refreshValues(tableName, metadata) {
    $.ajax({
      url: 'controllers/database/tables/refreshValues.php',
      type: 'GET',
      data: {
        tableName: tableName,
        columnMetadata: metadata,
        listFK: <?= json_encode($listFK) ?>,
        sort: sortStates[tableName]
      },
      success: function(response) {
        let newInputRow = $('#new-input-row-' + tableName).detach();
        $('#table-values-' + tableName).html(newInputRow);
        $('#table-values-' + tableName).append(response);
        adjustSpacer();
        updateInputWidth(tableName);
        filterTable(tableName);
      },
      error: function(response) {
        temporaryAlert('error', tableName, 'Impossible de contacter le serveur', 10);
      },
    });
  }

  //Scipt for the suggestion tooltip
  let currentInput = null;
  $(document).on('focus input', '.autofill', function() {
    currentInput = $(this);
    const query = currentInput.val();
    const table = currentInput.data('table');
    const column = currentInput.attr('id').replace('input-', '');
    const column_fk_list = <?= json_encode($listFK) ?>;

    let fkEntry = column_fk_list[table]?.find(entry => entry.column_name === column);
    if (!fkEntry) {
      return;
    }
    const offset = currentInput.offset();
    $('#tooltip').css({
      top: offset.top - 140 + currentInput.outerHeight(),
      left: offset.left,
      width: currentInput.outerWidth(),
      'max-height': '250px',
      'overflow-y': 'auto'
    }).show().text('Loading...');

    $.ajax({
      url: 'controllers/database/tables/suggestFK.php',
      method: 'GET',
      data: {
        value: query,
        fk_table: fkEntry.foreign_table_name,
        fk_column: fkEntry.foreign_column_name
      },
      success: function(data) {
        $('#tooltip').html(data);
      },
      error: function() {
        $('#tooltip').html('Error loading suggestions.');
      }
    });
  })
  $(document).on('click', '.suggestion-item', function() {
    if (currentInput) {
      currentInput.val($(this).text());
      $('#tooltip').hide();
    }
  });
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.autofill, #tooltip').length) {
      $('#tooltip').hide();
    }
  });

  const sortStates = {};

  function sortTable(tableName, columnName, metadata) {
    if (!sortStates[tableName]) sortStates[tableName] = [];
    if (sortStates[tableName][0] != columnName) sortStates[tableName][0] = columnName;

    const currentDirection = sortStates[tableName][1] || 'DESC';

    const newOrder = currentDirection === 'DESC' ? 'ASC' : 'DESC';
    sortStates[tableName][1] = newOrder;

    refreshValues(tableName, metadata);
  }

  //script to create and handle the error/warning/info/success messages
  let nbAlert = 0;

  function temporaryAlert(type, tableName, text, seconds = 5) {
    nbAlert++;
    const id = nbAlert
    switch (type) {
      case 'success':
        var content = "<div id='temporarySuccess" + id + "' class='w3-panel w3-green w3-opacity w3-display-container'><p>" + text + "</p>" + "<span onclick=\"this.parentElement.style.display='none'; adjustSpacer(); updateInputWidth('" + tableName + "');\" class=\"w3-button w3-display-topright\">&times;</span>" + "</div>";
        $('#alerts-' + tableName)
          .append(content);


        setTimeout(function() {
          $('#temporarySuccess' + id).remove();
          adjustSpacer();
          updateInputWidth(tableName);
        }, seconds * 1000);
        break;

      case 'warning':
        var content = "<div id='temporaryWarning" + id + "' class='w3-panel w3-yellow w3-opacity w3-display-container'><p>" + text + "</p>" + "<span onclick=\"this.parentElement.style.display='none'; adjustSpacer(); updateInputWidth('" + tableName + "');\" class=\"w3-button w3-display-topright\">&times;</span>" + "</div>";
        $('#alerts-' + tableName)
          .append(content);

        setTimeout(function() {
          $('#temporaryWarning' + id).remove();
          adjustSpacer();
          updateInputWidth(tableName);
        }, seconds * 1000);
        break;

      case 'error':
        var content = "<div id='temporaryError" + id + "' class='w3-panel w3-red w3-opacity w3-display-container'><p>" + text + "</p>" + "<span onclick=\"this.parentElement.style.display='none'; adjustSpacer(); updateInputWidth('" + tableName + "');\" class=\"w3-button w3-display-topright\">&times;</span>" + "</div>";
        $('#alerts-' + tableName)
          .append(content);

        setTimeout(function() {
          $('#temporaryError' + id).remove();
          adjustSpacer();
          updateInputWidth(tableName);
        }, seconds * 1000);
        break;

      case 'info':
        var content = "<div id='temporaryInfo" + id + "' class='w3-panel w3-blue w3-opacity w3-display-container'><p>" + text + "</p>" + "<span onclick=\"this.parentElement.style.display='none'; adjustSpacer(); updateInputWidth('" + tableName + "');\" class=\"w3-button w3-display-topright\">&times;</span>" + "</div>";
        $('#alerts-' + tableName)
          .append(content);


        setTimeout(function() {
          $('#temporaryInfo' + id).remove();
          adjustSpacer();
          updateInputWidth(tableName);
        }, seconds * 1000);
        break;
    }
    adjustSpacer();
    updateInputWidth(tableName);
  }

  //Script to show the div associated with a tab
  function chooseTab(event, category) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("w3-bar-item");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" w3-white w3-text-blue", " w3-blue");
    }
    event.currentTarget.className = event.currentTarget.className.replace(" w3-blue", " w3-white w3-text-blue");
    document.getElementById('div-' + category).style.display = "block";

    adjustSpacer();
    updateInputWidth(category);
  }

  //Script to filter the table using the filter-inputs
  function filterTable(tableName) {
    const inputs = document.querySelectorAll(`#filter-inputs-${tableName} .table-input`);
    const table = document.getElementById(`filter-table-${tableName}`);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName('td');
      let rowMatches = true;

      for (let j = 0; j < inputs.length; j++) {
        const inputValue = inputs[j].value.toLowerCase().trim();
        if (inputValue) {
          const cellText = cells[j] ? cells[j].textContent.toLowerCase() : '';
          if (!cellText.includes(inputValue)) {
            rowMatches = false;
            break;
          }
        }
      }

      rows[i].style.display = rowMatches ? '' : 'none';
    }
    updateInputWidth(tableName);
  }
  document.querySelectorAll('.filter-inputs').forEach(inputContainer => {
    const tableName = inputContainer.id.replace('filter-inputs-', '');
    inputContainer.querySelectorAll('input.table-input').forEach(input => {
      input.addEventListener('input', () => filterTable(tableName));
    });
  });

  //Script to update the width of the filter-inputs
  //based on the size of it's corresponding row in the table
  function updateInputWidth(tableName) {
    const table = document.getElementById(`filter-table-${tableName}`);
    const inputs = document.querySelectorAll(`#filter-inputs-${tableName} .table-input`);

    inputs.forEach((input, index) => {
      if (table.rows[0].cells[index]) {
        input.style.width = table.rows[0].cells[index].offsetWidth - 10 + 'px';
      }
    });
  };
  window.addEventListener('load', () => {
    document.querySelectorAll('.filter-inputs').forEach(inputContainer => {
      const tableName = inputContainer.id.replace('filter-inputs-', '');
      updateInputWidth(tableName);
      filterTable(tableName);
    });
  });
  window.addEventListener('resize', () => {
    document.querySelectorAll('.filter-inputs').forEach(inputContainer => {
      const tableName = inputContainer.id.replace('filter-inputs-', '');
      updateInputWidth(tableName);
    });
  });
</script>