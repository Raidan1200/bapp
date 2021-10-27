<x-email-layout>
  <h1 class="text-2xl">Ihre Abschlussrechnung</h1>

  <p class="my-2">Vielen Dank für Ihre Buchung beim {{ $order->venue->name }}.</p>

  <p class="my-2">
    Im Anhang erhalten Sie ihre Reservierungsbestätigung mit der Bitte
    die Anzahlung in Höhe von {{ money($order->interim_amount) }}€
    innerhalb der nächsten 7 Werktage zu überweisen.
    {{-- LATER Zahl konfigurierbar machen --}}
  </p>

  <p class="my-2">Aufgrund der großen Nachfrage können wir die Plätze leider nur eine Woche verbindlich für Sie reservieren.</p>

  <p class="my-2">Sollte die Anzahlung nicht erfolgen, werden die Plätze automatisch wieder vom System freigegeben.</p>

  <p class="my-2">Vielen Dank für ihr Verständnis.</p>

</x-email-layout>
