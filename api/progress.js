const { getSupabase } = require('./_lib/supabase');
const { syncToGoHighLevel } = require('./_lib/ghl');

// Helper para configurar CORS
function setCorsHeaders(res) {
  res.setHeader('Access-Control-Allow-Credentials', 'true');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
  res.setHeader(
    'Access-Control-Allow-Headers',
    'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version'
  );
}

module.exports = async function handler(req, res) {
  setCorsHeaders(res);

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    const supabase = getSupabase();

    // ----------------------------------------------------
    // GET: Obtener el progreso del estudiante por email
    // ----------------------------------------------------
    if (req.method === 'GET') {
      const email = (req.query.email || '').trim().toLowerCase();

      if (!email || !email.includes('@')) {
        return res.status(400).json({ ok: false, error: 'Email requerido o inválido.' });
      }

      // Buscar estudiante
      const { data: student, error: studentError } = await supabase
        .from('students')
        .select('*')
        .ilike('email', email)
        .maybeSingle();

      if (studentError) {
        throw studentError;
      }

      if (!student) {
        return res.status(200).json({
          ok: true,
          exists: false,
          student: null,
          checks: {}
        });
      }

      // Obtener tareas completadas
      const { data: progressRows, error: progressError } = await supabase
        .from('student_progress')
        .select('task_key, completed')
        .eq('student_id', student.id)
        .eq('completed', true);

      if (progressError) {
        throw progressError;
      }

      const checks = {};
      (progressRows || []).forEach(row => {
        checks[row.task_key] = true;
      });

      return res.status(200).json({
        ok: true,
        exists: true,
        student: {
          id: student.id,
          email: student.email,
          fullName: student.full_name,
          system: student.system,
          residence: student.residence
        },
        checks
      });
    }

    // ----------------------------------------------------
    // POST: Guardar o actualizar progreso / perfil
    // ----------------------------------------------------
    if (req.method === 'POST') {
      const body = typeof req.body === 'string' ? JSON.parse(req.body) : (req.body || {});
      const email = (body.email || '').trim().toLowerCase();

      if (!email || !email.includes('@')) {
        return res.status(400).json({ ok: false, error: 'Email requerido o inválido.' });
      }

      const system = ['medium', 'elite'].includes(body.system) ? body.system : 'medium';
      const residence = ['usa', 'outside'].includes(body.residence) ? body.residence : 'usa';
      const fullName = (body.name || '').trim();

      // 1. Upsert del estudiante
      const studentPayload = {
        email,
        system,
        residence,
        last_active_at: new Date().toISOString()
      };
      if (fullName) studentPayload.full_name = fullName;

      const { data: student, error: studentError } = await supabase
        .from('students')
        .upsert(studentPayload, { onConflict: 'email' })
        .select()
        .single();

      if (studentError) {
        throw studentError;
      }

      // 2. Si se envió una tarea específica para actualizar
      if (body.taskKey) {
        const isCompleted = body.completed === true;

        const { error: taskError } = await supabase
          .from('student_progress')
          .upsert(
            {
              student_id: student.id,
              task_key: body.taskKey,
              completed: isCompleted,
              completed_at: new Date().toISOString()
            },
            { onConflict: 'student_id,task_key' }
          );

        if (taskError) {
          throw taskError;
        }
      }

      // 3. Sincronizar en segundo plano con GoHighLevel si se incluyeron estadísticas
      if (body.stats) {
        // No bloqueamos la respuesta esperando a GHL
        syncToGoHighLevel({
          student,
          stats: body.stats,
          lastTaskKey: body.taskKey
        }).catch(err => console.error('[GHL Sync Background Error]:', err));
      }

      return res.status(200).json({
        ok: true,
        studentId: student.id,
        saved: true
      });
    }

    return res.status(405).json({ ok: false, error: 'Método no permitido' });
  } catch (err) {
    console.error('[API Progress Error]:', err);
    return res.status(500).json({ ok: false, error: err.message || 'Error interno del servidor' });
  }
};
