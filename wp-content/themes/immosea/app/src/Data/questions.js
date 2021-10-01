export const questions = [
  {
    title: 'Um was für eine Immobilie handelt es sich?',
    component: 'Step1',
  },
  {
    title: 'Möchten Sie Ihre Immobilie verkaufen oder vermieten?',
    component: 'Step2'
  },
  {
    title: 'Aus welchem Baujahr stammt Ihr Objekt?',
    component: 'Step3'
  },
  {
    title: 'Schreiben Sie Ihre Werbetexte selbst?',
    component: 'Step4',
    text: `<p>Eine aussagefähige Objektbeschreibung erhöht die Quantität und Qualität der Anfragen auf dein Immobilieninserat.</p>
        <p>Bei Immosea Erhalten Sie Ihren Werbetext von erfahrenen Immobilienexperten mit langjähriger 
        Marketing-Erfahrung.</p>`
  },
  {
    title: 'Womit können wir Sie zusätzlich bei der Vermarktung Ihrer Immobilie unterstützen?',
    component: 'Step5',
    showPrice: true
  },
  {
    title: 'Damit wir Ihr Objekt besser verstehen, benötigen wir ein paar weitere Informationen.',
    component: 'Step6',
    showPrice: true
  },
  {
    title: 'Daten deines Energieausweises:',
    component: 'Step7',
    text: 'Für eine rechtssichere Vermarktung deiner Immobilie benötigen Sie du einen Energieausweis',
    showPrice: true
  },
  {
    title: 'Möchtest du dennoch einen Energieausweis für deine Immobilie erhalten?',
    text: 'Du hast angegeben, dass für dein Objekt Denkmalschutz besteht. Daher ist für deine Immobilie kein' +
      ' Energieausweis erforderlich.',
    component: 'Step8',
    showPrice: true
  },
  {
    title: 'Damit wir das Exposé für Sie erstellen können, benötigen wir noch Ihre Werbetexte:',
    component: 'Step9',
    showPrice: true
  },
  {
    title: 'Für eine ansprechende Objektbeschreibung benötigen wir noch ein paar weitere Informationen:',
    component: 'Step10',
    showPrice: true
  },
  {
    title: 'Wie lautet die Anschrift Ihrer Immobilie?',
    text: 'Keine Sorge, wir verwenden diese Information lediglich, um die Objektlage im Exposé optimal zu beschreiben und / oder, um die Objektfotografie zu beauftragen.',
    component: 'Step11',
    showPrice: true
  },
  {
    title: 'Für die Erstellung des Exposés benötigen wir noch deine Objektbilder und Grundrisse.',
    text: 'Wird nicht benötigt, wenn du mit deiner Bestellung die Erstellung dieser Dateien in Auftrag gibst.',
    component: 'Step12',
    showPrice: true
  },
  {
    title: 'Dein Grundriss:',
    text: 'Eingescannt, abfotografiert, handschriftliche Skizze, o. ä.',
    component: 'Step13',
    showPrice: true
  },
  {
    title: 'Zu guter Letzt benötigen wir noch deine Kontaktdaten, um deinen Auftrag bearbeiten zu können.',
    component: 'Step14'
  },
  {
    title: 'Bestellung überprüfen',
    component: 'Step15'
  }
]