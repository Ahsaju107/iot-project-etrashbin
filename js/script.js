const gaugeElement = document.querySelector(".gauge");

function setGaugeValue(gauge, value) {
  if (value < 0 || value > 1) {
    return;
  }

  gauge.querySelector(".gauge__fill").style.transform = `rotate(${
    value / 2
  }turn)`;

}

async function updateGauge() {
    try {
      const res = await fetch('../data.php');
      const obj = await res.json();       // HARUS valid JSON
      if (obj.status === 'ok') {
        setGaugeValue(gaugeElement, obj.kapasitasOrganik / 100);
      } else {
        console.error('Server error:', obj.msg);
      }
    } catch (e) {
      console.error('Fetch gagal:', e);
    }
  }

  updateGauge();
setInterval(updateGauge, 2000);