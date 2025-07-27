# IFAK Ticketsystem
Internes Ticketsystem für den IT-Support des IFAK e.V.

## Warum
Weil wir es können und getestete vorgefertigte Systeme nicht zu uns passten.

## Status
Funktionsfähige Beta

## Inhalt
- Login per Token – Agents melden sich über ein vorgegebenes persönliches Token an (siehe login.php) und können sich jederzeit abmelden Absichtlich keine Benutzerverwaltung
- Dashboard – Übersicht aller Tickets mit Filtern für Team, Status, Agent und Suche. Ältere oder unzugewiesene Tickets werden hervorgehoben.
- Ticket-Erstellung – Ein Formular erlaubt das Anlegen neuer Tickets inklusive Priorität, Teamzuordnung, Kontaktinformationen und Anhängen (jpg, png, gif, pdf usw.). Es besteht eine Kontakt-Suche mit Autocomplete über das (Unternehmens)Adressbuch.
- Ticket-Ansicht – Detailseite mit Kontakt- und Organisationsdaten, Anhängen (Bildvorschau per Lightbox) sowie Kommentarverlauf mit Markierung einer Lösung. Auch verwandte Tickets derselben Person, Einrichtung oder Standort werden angezeigt.
- Zuweisungen und Statusaktualisierung – Tickets lassen sich Agenten zuweisen und im Status bzw. in der Priorität ändern. Kommentare können optional als Lösung markiert werden.
- Verfügbarkeitskalender – Jeder Agent kann seine Verfügbarkeit für drei Monate pflegen. Eine Übersicht zeigt alle Agenten samt farbigen Statuskästchen für die aktuelle und nächste Woche.
- REST‑ähnliche APIs – Endpunkte für Ticket- und Mitarbeitersuche sowie Detailabruf eines Mitarbeiters.
- Service Worker für Benachrichtigungen – Registrierung eines Service Workers in main.js für Push-Benachrichtigungen.

## Kontakt
IFAK e.V. - IT-Abteilung
