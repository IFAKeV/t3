# QA: Lösungseintrag ohne Kommentar verhindern

1. Melde dich im System an und öffne ein bestehendes Ticket, das noch nicht als gelöst markiert ist.
2. Scrolle zum Formular "Aktualisieren" und aktiviere die Checkbox **Als Lösung markieren**.
3. Lass das Kommentarfeld leer und klicke auf **Aktualisieren**.
   - Erwartet: Der Submit wird client-seitig mit einem Hinweis unterbunden.
4. Trage anschließend einen Kommentar mit beliebigem Text ein, lass die Checkbox aktiviert und sende das Formular erneut ab.
   - Erwartet: Der Server akzeptiert die Eingabe und speichert die Lösung.
5. Wiederhole Schritt 2 und 3, indem du JavaScript deaktivierst (z. B. über die Browser-Entwicklertools) oder die Anfrage direkt per Netzwerk-Tool sendest.
   - Erwartet: Der Server bricht mit der Fehlermeldung *„Bitte gib einen Kommentar ein, bevor du eine Lösung speicherst.“* ab und zeigt das Formular mit dem zuvor eingegebenen Text erneut an.
