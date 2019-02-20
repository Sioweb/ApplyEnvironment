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

## Environment per Konsole

Für automatische Builds kann es wichtig werden, die Environments per Konsole zu installieren. Im Root von Contao kann folgendes in die Konsole eingegeben werden:

	vendor/bin/contao-console --env=YOUR_ENVIRONMENT sioweb:environment

Auf einigen Systemen ist PHP nicht als Alias angelegt, dann muss dieses vor den Befehl geschrieben werden:


	/path/to/php vendor/bin/contao-console --env=YOUR_ENVIRONMENT sioweb:environment

## Composer install/update

Die aktuelle Environment, kann auch direkt mit `composer update` und `composer install` eingespielt werden:

	{
	    "scripts": {
		"post-install-cmd": [
		    "Contao\\ManagerBundle\\Composer\\ScriptHandler::initializeApplication",
		    "Sioweb\\ApplyEnvironment\\Composer\\ApplyEnvironment::setup"
		],
		"post-update-cmd": [
		    "Contao\\ManagerBundle\\Composer\\ScriptHandler::initializeApplication",
		    "Sioweb\\ApplyEnvironment\\Composer\\ApplyEnvironment::setup"
		]
	    },
	}

### .env

Damit die korrekte Environment eingespielt wird, muss diese im Root von Contao in einer `.env`-Datei hinterlegt werden:

	APPLY_ENVIRONMENT='localhost'
	
Statt localhost, bitte die Environment angeben die ihr Wünscht, das ist wichtig, damit die korrekte Datenbank verwendet wird.

### MYSQL_USER not found

Wenn die Datenbank wie folgt in der Datei `/app/config/parameters_XXX.yml` definiert sind, kann es passieren, dass die Konsole keinen Zugriff auf die ENV-Daten hat:

	# This file has been auto-generated during installation
	parameters:
	    database_user: "%env(MYSQL_USER)%"
	    database_password: "%env(MYSQL_PASSWORD)%"
	    database_name: "%env(MYSQL_DATABASE)%"

Der Konsole müssen in diesem Fall erst die Daten übermittelt werden: 

	export MYSQL_DATABASE="your_database"
	export MYSQL_USER="your_username"
	export MYSQL_PASSWORD="your_password"

## Wie werden einstellungen gespeichert?

Überall im Contao-Backend, werden alle Widgets um einen kleinen unsichtbaren Kreis erweitert. Wird die Maus über ein Eingabefeld bewegt, wird der Kreis sichtbar. Durch einen Klick öffnet sich das Menü mit den möglichen Environments / Umgebungen. Durch einen Klick auf eine Environment, wird der Eintrag in dem Eingabefeld gespeichert. 

**Hinweis:** Das Formular muss nicht abgesendet werden, damit die Einträge gespeichert wird.

## Environment einspielen

In der Systemwartung befindet sich nun ein neues Eingabefeld. Wurden die `short`-Einstellungen korrekt in der `environments.yml` notiert, wird die aktuelle Environment-Einstellung hier vorausgewählt. Dazu empfiehlt es sich, in der `.htaccess` die dev/localhost-Domain entsprechend auf `app_dev.php` oder auch `app_localhost.php` automatisch umzuleiten.

Durch absenden der Environment werden nun alle gespeicherten Daten aus der ausgewählten Environment eingespielt.

## Wo werden die Daten gespeichert?

Die Daten werden unter `/app/environments/` gespeichert. 
