# Apply Environments

Es kommt vor, dass jede Environment (Live, Dev, Local, ...) andere Einstellungen benötigt. Am häufigsten sind davon die Domain- und HTTPS-Einstellungen in den Startpunkten betroffen.

## Beispiele

- Live: HTTPS ja / www.domain.tld
- Dev: HTTPS nein / dev.domain.tld
- Localhost: HTTPS nein / domain.localhost

## Einstellungen

Im Verzeichnis `/app/config/` muss eine Datei `environments.yml` erzeugt werden. Für Live, Dev und Localhost muss die Datei wie folgt aufgebaut werden:

	apply_environments:
		environments:
			productive:
				prod: true
				short: intern
				title: Produktiv
			development:
				short: dev
				title: Development
			localhost:
				title: Localhost

### Optionen

<table width="100%">
	<tr>
		<th>Option</th>
		<th>Werte</th>
		<th>Beschreibung</th>
	</tr>
	<tr>
		<td>prod (optional)</td>
		<td>true|false</td>
		<td>Dieser Wert definiert diese Umgebung als Live-Umgebung</td>
	</tr>
	<tr>
		<td>short (optional)</td>
		<td>(string)</td>
		<td>Dieser Wert sollte den Standard-Bezeichnungen prod / dev / localhost entsprechen, wird er leer gelassen, wird automatisch der übergeornete Schlüssel verwendet (dev entspräche im obigen Beispiel development)</td>
	</tr>
	<tr>
		<td>title</td>
		<td>(string)</td>
		<td>Hier muss ein beschreibender Titel eingetragen werden. Das Plugin gibt die Titel in diversen Dropdowns aus</td>
	</tr>
</table>

## Wie werden einstellungen gespeichert?

Überall im Contao-Backend, werden alle Widgets um einen kleinen unsichtbaren Kreis erweitert. Wird die Maus über ein Eingabefeld bewegt, wird der Kreis sichtbar. Durch einen Klick öffnet sich das Menü mit den möglichen Environments / Umgebungen. Durch einen Klick auf eine Environment, wird der Eintrag in dem Eingabefeld gespeichert. 

**Hinweis:** Das Formular muss nicht abgesendet werden, damit die Einträge gespeichert wird.

## Environment einspielen

In der Systemwartung befindet sich nun ein neues Eingabefeld. Wurden die `short`-Einstellungen korrekt in der `environments.yml` notiert, wird die aktuelle Environment-Einstellung hier vorausgewählt. Dazu empfiehlt es sich, in der `.htaccess` die dev/localhost-Domain entsprechend auf `app_dev.php` oder auch `app_localhost.php` automatisch umzuleiten.

Durch absenden der Environment werden nun alle gespeicherten Daten aus der ausgewählten Environment eingespielt.

## Wo werden die Daten gespeichert?

Die Daten werden unter `/app/environments/` gespeichert. 
