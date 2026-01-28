Inter-App Communication (Training)

Beschreibung des Event-Modells und Beispiele für Training.

Beispiel Sender:
```
$eventService->dispatchCallbackEvent(['eventId'=>42], function($resp){ /* ... */ });
```
