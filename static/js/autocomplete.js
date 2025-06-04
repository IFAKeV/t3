// Erweiterte Funktionalität für die Kontaktsuche mit Autocomplete
document.addEventListener('DOMContentLoaded', function() {
    // Prüfen, ob das Suchfeld vorhanden ist
    const contactSearch = document.getElementById('contact_search');
    if (!contactSearch) return;
    
    // Prüfen, ob jQuery und jQuery UI vorhanden sind
    if (typeof jQuery === 'undefined' || typeof jQuery.ui === 'undefined') {
        console.error('jQuery oder jQuery UI ist nicht geladen!');
        return;
    }
    
    // Autocomplete-Funktion für das Suchfeld
    $(contactSearch).autocomplete({
        source: function(request, response) {
            // API-Anfrage an den Server
            $.ajax({
                url: "/api/search_employees",
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function(data) {
                    // Ergebnisformat:
                    // [ { id: 123, name: "Max Mustermann", phone: "...", email: "..." }, ... ]
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.error("Fehler bei der Suche:", error);
                    response([]);
                }
            });
        },
        minLength: 2,  // Mindestens 2 Zeichen für die Suche
        
        // Bei Auswahl eines Kontakts
        select: function(event, ui) {
            // Ausgewählten Kontakt in die Felder einfügen
            $('#contact_name').val(ui.item.name);
            $('#contact_phone').val(ui.item.phone);
            $('#contact_email').val(ui.item.email);
            
            // Kontaktdetails anzeigen
            $('#contact_details').show();
            
            // Autocomplete-Input leeren aber Suche beenden
            $(this).val('');
            return false;
        }
    })
    
    // Anpassung der Darstellung der Suchergebnisse
    .autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div class='autocomplete-item'>" + 
                    "<div class='autocomplete-name'>" + item.name + "</div>" +
                    (item.email ? "<div class='autocomplete-email'>" + item.email + "</div>" : "") +
                    "</div>")
            .appendTo(ul);
    };
    
    // Styling für die Autocomplete-Liste
    $("<style>")
        .prop("type", "text/css")
        .html(`
            .ui-autocomplete {
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                border: 1px solid #ddd;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .autocomplete-item {
                padding: 5px;
            }
            .autocomplete-name {
                font-weight: bold;
            }
            .autocomplete-email {
                font-size: 0.9em;
                color: #666;
            }
        `)
        .appendTo("head");
});
