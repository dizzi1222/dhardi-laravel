Diego Samuel Härdi Santana
Full-Stack-Entwickler · DevOps
Jarabacoa, La Vega, Republik Dominikanisch · UTC−4
diegosamuel042@gmail.com · +1 829 821 6385
github.com/dizzi1222 · linkedin.com/in/diego-samuel-härdi-santana-3a4343428

An Intrinsa AG
z. Hd. Daniel

Jarabacoa, 6. Oktober 2026

Betreff: Postulation als Full-Stack-Entwickler (Laravel / React)

Sehr geehrter Herr Daniel,

Sie haben eine Stelle ausgeschrieben, deren wichtigste Anforderung nicht in
einer Werkzeugliste steht, sondern in einem Satz: Laravel in Produktion
ausgeliefert und betrieben. Genau darum bewerbe ich mich, und genau darum
schicke ich Ihnen kein Motivationsschreiben, sondern ein Repository.

Sie schreiben, ein Beispiel meiner Arbeit sei Ihnen wichtiger als Papier. Hier
ist meines, und es läuft:

  github.com/dizzi1222/dhardi-laravel
  dhardi-laravel.vercel.app

Es ist ein Laravel-13-Backend (PHP 8.4) über Inertia 3 mit React 19 und
TypeScript im Strict-Modus, dreisprachig, ausgeliefert als Container auf Vercel.

Was ich offen sage, bevor Sie es selbst herausfinden

Mein produktiver Backend-Schwerpunkt lag bisher nicht auf Laravel. Er lag auf
Node.js mit Express, TypeORM und PostgreSQL — bei PTD-Talento, dem
Talent-Marketplace des Cincinnatus Institute of Craftsmanship, wo ich als
Lead Tech in einem Team von vier über 18 Sprints und 74 gemergte Pull Requests
die Entwicklungstaktung vorgegeben habe.

Diese Anwendung ist meine erste produktive Laravel-Basis. Sie entstand in
öffentlich, mit laufender Testsuite und echtem Monitoring, nicht in einem
privaten Repository. Ich sage das an erster Stelle, weil ein Lebenslauf, der
etwas behauptet, das der erste Commit widerlegt, in einem technischen Gespräch
ohnehin nicht lange hält.

Was die Anwendung belegt

Mandantenfähigkeit pro Instanz. Ein Deployment pro Kunde, Mandant aus der
Umgebung aufgelöst und per Global Scope auf jede Query gesetzt, damit die
Trennung nicht von Disziplin abhängt. `php artisan tenant:provision` erstellt
eine neue Kundeninstanz in einem Befehl — das entspricht Ihrem Modell «jeder
Kunde auf einer eigenen Instanz».

Ein Assistent als reales Feature, nicht als Demo. Er läuft über ein Gateway mit
Retry mit Backoff und Jitter, Circuit Breaker pro Backend, Fallback-Kette,
Cache pro Mandant und Guardrails auf Eingabe und Ausgabe. Jeder Versuch landet
in einer Tabelle; die Zahlen auf der Seite sind Abfragen auf diese Tabelle, also
Schätzungen. Ein Golden Dataset aus zwölf Fällen läuft in der CI und lässt den
Build fehlschlagen, wenn ein Prompt- oder Modellwechsel die erwarteten Angaben
verfälscht.

84 Tests. Unit-Tests für die Backoff-Kurve, den Circuit Breaker, die Guardrails
und die Cache-Schlüssel, Feature-Tests für Tenant-Auflösung, Endpunkte und
Streaming, plus eine Abdeckung der Übersetzungen in allen drei Sprachen.

End-to-End verifiziert. Routen, alle drei Sprachen, Streaming über
Server-Sent-Events und die Ablehnung von Prompt-Injection sind gegen die
laufende Instanz geprüft, nicht nur gegen die Testsuite.

Warum ich zu Ihnen möchte

Sie haben LLM-Features dort, wo das Produkt noch schwach ist — der AI Companion
ist der Punkt, an dem jemand fehlt. Mich interessiert die Frage nicht, wie man
einen Modellaufruf macht. Die interessiert mich in der Form, wie das System
darauf reagiert, wenn er ausfällt:Retry, Fallback, Circuit Breaker, ein
Messsystem, das die Frage nach einem Vorfall beantwortbar macht. Das ist der Teil,
den ich bereits gebaut habe.

Was ich offen nicht behaupte

Mein Deutsch ist nicht meine Muttersprache. Ich schreibe es und verstehe
Fachtext ohne Mühe, aber flüssige Kundengespräche auf dem Niveau, das ein
Enterprise-Kunde in der DACH-Region erwartet, kann ich noch nicht liefern. Ich
arbeite aktiv daran. Wenn Sie ein Probegespräch auf Deutsch wollen, sagen Sie
es — dann wissen Sie sofort, wo ich stehe.

Ich wohne in der Dominikanischen Republik, UTC−4, also nicht in der DACH-Region.
Als Schweizer Staatsbürger brauche ich für eine Anstellung durch ein Schweizer
Unternehmen keine Arbeitsbewilligung und kann sofort beginnen. Die Überlappung
mit CET/CEST liegt am Vormittag. Für Termine vor Ort plane ich Vorlauf ein.

Ich antworte innerhalb eines Tages. Wenn Sie lieber sprechen als schreiben:
+41 79 933 66 22 ist Ihre Nummer, meine ist +1 829 821 6385.

Mit freundlichen Grüßen
Diego Samuel Härdi Santana