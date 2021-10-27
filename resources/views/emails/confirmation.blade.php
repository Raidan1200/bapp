<x-email-layout>
  <h1 class="text-2xl">Ihre Anzahlungsbestätigung</h1>

  <p class="my-2">Vielen Dank für Ihre Buchung beim {{ $order->venue->name }}.</p>

  <p class="my-2">
    Ihre Anzahlung für die gebuchte Reservierung haben wir erhalten
    und bestätigen hiermit verbindlich ihre Reservierung für den
    {{ $order->starts_at->timezone('Europe/Berlin')->format('d.m.Y') }}.
  </p>

  <p class="my-2">
    Sollte sich noch etwas bezüglich der Personenanzahl ändern,
    bitten wir Sie uns dies bis spätestens 14 Tage vor Veranstaltung schriftlich mitzuteilen.
  </p>

</x-email-layout>
