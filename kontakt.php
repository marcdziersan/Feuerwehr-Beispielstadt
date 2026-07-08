<?php
declare(strict_types=1);

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$captchaA = random_int(2, 12);
$captchaB = random_int(2, 12);
$_SESSION['captcha_result'] = $captchaA + $captchaB;

$csrfToken = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$captchaQuestion = htmlspecialchars($captchaA . ' + ' . $captchaB, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="de" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kontakt | Freiwillige Feuerwehr Beispielstadt</title>
  <meta name="description" content="Kontakt zur Freiwilligen Feuerwehr Beispielstadt.">
  <meta name="theme-color" content="#b20d1e">
  <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="">
  <a class="skip-link" href="#main">Zum Inhalt springen</a>
  <header class="site-header" data-header>
    <div class="container header-inner">
      <a class="brand" href="index.html" aria-label="Freiwillige Feuerwehr Beispielstadt Startseite">
        <img src="assets/img/logo.svg" alt="" width="46" height="46">
        <span><strong>FF Beispielstadt</strong><small>Retten · Löschen · Bergen · Schützen</small></span>
      </a>
      <button class="nav-toggle" type="button" aria-label="Menü öffnen" aria-expanded="false" data-nav-toggle>
        <span></span><span></span><span></span>
      </button>
      <nav class="main-nav" data-main-nav aria-label="Hauptnavigation">
        <a href="index.html">Startseite</a>
        <a href="ueber-uns.html">Über uns</a>
        <a href="gruppen.html">Gruppen</a>
        <a href="mitglieder.html">Mitglieder</a>
        <a href="fahrzeuge.html">Fahrzeuge</a>
        <a href="einsaetze.html">Einsätze</a>
        <a href="termine.html">Termine</a>
        <a href="brandschutz.html">Brandschutz</a>
        <a href="kontakt.php" aria-current="page" class="active">Kontakt</a>
      </nav>
      <button class="theme-toggle" type="button" data-theme-toggle aria-label="Darstellung wechseln">◐</button>
    </div>
  </header>

  <main id="main">
    <section class="page-hero section-bleed">
      <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="index.html">Startseite</a><span>›</span><span>Kontakt</span>
        </nav>
        <p class="kicker">Ansprechpartner</p>
        <h1>Kontakt</h1>
        <p>Für allgemeine Fragen, Mitgliedschaft, Jugendfeuerwehr, Förderverein oder Presseanfragen. Im Notfall bitte immer sofort die 112 wählen.</p>
      </div>
    </section>

    <section class="section">
      <div class="container grid grid-2">
        <article class="card reveal">
          <h2>Nachricht senden</h2>
          <p>Deine Nachricht wird nicht per E-Mail versendet, sondern sicher in einer lokalen SQLite-Datenbank gespeichert.</p>

          <form class="form" action="php/kontakt_senden.php" method="post" data-contact-form>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="field hp-field" aria-hidden="true">
              <label for="website">Website</label>
              <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <div class="field">
              <label for="name">Name *</label>
              <input id="name" name="name" type="text" required maxlength="120" autocomplete="name">
            </div>

            <div class="field">
              <label for="email">E-Mail *</label>
              <input id="email" name="email" type="email" required maxlength="190" autocomplete="email">
            </div>

            <div class="field">
              <label for="topic">Thema</label>
              <select id="topic" name="topic">
                <option>Allgemeine Anfrage</option>
                <option>Mitgliedschaft</option>
                <option>Jugendfeuerwehr</option>
                <option>Brandschutzerziehung</option>
                <option>Förderverein</option>
                <option>Presse</option>
              </select>
            </div>

            <div class="field">
              <label for="message">Nachricht *</label>
              <textarea id="message" name="message" required maxlength="5000"></textarea>
            </div>

            <div class="field captcha-field">
              <label for="captcha">Sicherheitsfrage: Was ist <?= $captchaQuestion ?>? *</label>
              <input id="captcha" name="captcha" type="number" inputmode="numeric" required autocomplete="off">
            </div>

            <p class="form-note">Pflichtfelder sind mit * markiert. Das Captcha und der CSRF-Token werden serverseitig erzeugt.</p>
            <button class="btn btn-primary" type="submit">Nachricht sicher speichern</button>
            <p class="form-note" data-form-status></p>
          </form>
        </article>

        <aside class="card reveal delay-1">
          <h2>Kontaktinformationen</h2>
          <p><strong>Freiwillige Feuerwehr Beispielstadt</strong><br>
          Gerätehaus Musterstraße 12<br>
          12345 Beispielstadt</p>

          <p><strong>Telefon Gerätehaus:</strong><br>
          01234 / 567890</p>

          <p><strong>E-Mail:</strong><br>
          kontakt@feuerwehr-beispielstadt.de</p>

          <div class="warning">
            <strong>Notfall?</strong><br>
            Bei Feuer, Unfall, akuter Gefahr oder medizinischem Notfall niemals das Kontaktformular nutzen, sondern sofort <strong>112</strong> wählen.
          </div>

          <div class="actions">
            <a class="btn btn-primary" href="notruf.html">Notruf 112</a>
            <a class="btn btn-ghost" href="mitmachen.html">Mitmachen</a>
          </div>
        </aside>
      </div>
    </section>

    <a class="admin-lock" href="admin/index.php" aria-label="Adminbereich öffnen" title="Adminbereich">🔒</a>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <section>
        <div class="footer-brand"><img src="assets/img/logo.svg" alt="" width="42" height="42"><strong>Freiwillige Feuerwehr Beispielstadt</strong></div>
        <p>Eine ausbaufähige Muster-Webseite für eine freiwillige Feuerwehr. Inhalte, Namen, Adressen und Einsatzdaten sind Platzhalter.</p>
      </section>
      <section>
        <h2>Direktlinks</h2>
        <a href="notruf.html">Notruf 112</a>
        <a href="einsaetze.html">Einsätze</a>
        <a href="termine.html">Dienstplan</a>
        <a href="kontakt.php">Kontakt</a>
      </section>
      <section>
        <h2>Rechtliches</h2>
        <a href="impressum.html">Impressum</a>
        <a href="datenschutz.html">Datenschutz</a>
        <a href="barrierefreiheit.html">Barrierefreiheit</a>
        <a href="sitemap.xml">Sitemap</a>
      </section>
      <section>
        <h2>Kontakt</h2>
        <p><strong>Im Notfall: 112</strong><br>Gerätehaus Musterstraße 12<br>12345 Beispielstadt<br><a href="mailto:kontakt@feuerwehr-beispielstadt.de">kontakt@feuerwehr-beispielstadt.de</a></p>
      </section>
    </div>
    <div class="container footer-bottom">
      <span>© 2026 Freiwillige Feuerwehr Beispielstadt</span>
      <span>Erstellt als HTML/CSS/JS/PHP/SQLite-Projekt · Keine externen Bibliotheken</span>
    </div>
  </footer>
  <button class="to-top" data-to-top aria-label="Nach oben">↑</button>
  <script src="assets/js/main.js"></script>
</body>
</html>
