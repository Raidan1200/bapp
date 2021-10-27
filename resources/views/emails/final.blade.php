<x-email-layout>
  <h1 class="text-2xl">Ihre Gesamtrechnung</h1>

  <p class="my-2">Vielen Dank für Ihre Buchung beim {{ $order->venue->name }}.</p>

  <p class="my-2">
    Bitte überweisen Sie {{ money($order->deposit_amount) }}€
    innerhalb der nächsten 7 Werktage.
    {{-- LATER Zahl konfigurierbar machen --}}
  </p>

  <p class="my-2">Vielen Dank für ihr Verständnis.</p>

</x-email-layout>
