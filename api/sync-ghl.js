const { getSupabase } = require('./_lib/supabase');
const { syncToGoHighLevel } = require('./_lib/ghl');

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ ok: false, error: 'Método no permitido. Usa POST.' });
  }

  try {
    const body = typeof req.body === 'string' ? JSON.parse(req.body) : (req.body || {});
    const email = (body.email || '').trim().toLowerCase();

    if (!email) {
      return res.status(400).json({ ok: false, error: 'Email requerido.' });
    }

    const supabase = getSupabase();

    // 1. Obtener estudiante
    const { data: student, error: studentError } = await supabase
      .from('students')
      .select('*')
      .ilike('email', email)
      .single();

    if (studentError || !student) {
      return res.status(404).json({ ok: false, error: 'Estudiante no encontrado.' });
    }

    // 2. Obtener tareas completadas
    const { data: tasks, error: tasksError } = await supabase
      .from('student_progress')
      .select('task_key')
      .eq('student_id', student.id)
      .eq('completed', true);

    if (tasksError) throw tasksError;

    const completedTasksCount = (tasks || []).length;
    // Cálculo aproximado si no se envían stats detalladas
    const stats = body.stats || {
      completedTasks: completedTasksCount,
      completedStations: Math.min(12, Math.floor(completedTasksCount / 4)),
      percentage: Math.min(100, Math.round((completedTasksCount / 48) * 100)),
      currentStationName: body.currentStationName || 'En progreso'
    };

    // 3. Sincronizar
    const ghlResponse = await syncToGoHighLevel({
      student,
      stats,
      lastTaskKey: body.lastTaskKey || ''
    });

    return res.status(200).json({
      ok: true,
      message: 'Sincronización con GoHighLevel completada',
      ghlResponse
    });
  } catch (err) {
    console.error('[API sync-ghl Error]:', err);
    return res.status(500).json({ ok: false, error: err.message });
  }
};
