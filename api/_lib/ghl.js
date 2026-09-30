// Sincronización con GoHighLevel (GHL)
// Soporta:
// 1. GHL Webhook (Workflows en GHL)
// 2. GHL API v2 (Upsert Contact)

async function syncToGoHighLevel({ student, stats, lastTaskKey }) {
  const webhookUrl = process.env.GHL_WEBHOOK_URL;
  const apiKey = process.env.GHL_API_KEY;
  const locationId = process.env.GHL_LOCATION_ID;

  const payload = {
    email: student.email,
    name: student.full_name || '',
    system: student.system,
    residence: student.residence,
    completedStations: stats.completedStations || 0,
    totalStations: 12,
    completedTasks: stats.completedTasks || 0,
    progressPercentage: stats.percentage || 0,
    currentStationName: stats.currentStationName || '',
    currentMilestone: stats.currentMilestone || '',
    lastTaskKey: lastTaskKey || '',
    lastActiveAt: new Date().toISOString(),
    event: 'vcp_route_progress_updated'
  };

  const results = { webhook: null, api: null };

  // 1. Enviar a Webhook de GoHighLevel si está configurado
  if (webhookUrl) {
    try {
      const res = await fetch(webhookUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      results.webhook = { status: res.status, ok: res.ok };
    } catch (err) {
      console.error('[GHL Webhook Error]:', err.message);
      results.webhook = { error: err.message };
    }
  }

  // 2. Enviar a API v2 de GoHighLevel si se tienen credenciales directas
  if (apiKey && locationId) {
    try {
      const ghlApiUrl = 'https://services.leadconnectorhq.com/contacts/upsert';
      const ghlBody = {
        email: student.email,
        name: student.full_name,
        locationId: locationId,
        customFields: [
          { key: 'vcp_estacion_actual', value: stats.currentStationName || '' },
          { key: 'vcp_estaciones_completas', value: String(stats.completedStations || 0) },
          { key: 'vcp_progreso_porcentaje', value: `${stats.percentage || 0}%` },
          { key: 'vcp_tareas_completadas', value: String(stats.completedTasks || 0) },
          { key: 'vcp_sistema', value: student.system },
          { key: 'vcp_residencia', value: student.residence }
        ],
        tags: [
          `vcp-sistema-${student.system}`,
          `vcp-estacion-${stats.completedStations}`
        ]
      };

      const res = await fetch(ghlApiUrl, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Version': '2021-07-28',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(ghlBody)
      });

      const data = await res.json().catch(() => ({}));
      results.api = { status: res.status, ok: res.ok, data };
    } catch (err) {
      console.error('[GHL API Error]:', err.message);
      results.api = { error: err.message };
    }
  }

  return results;
}

module.exports = { syncToGoHighLevel };
