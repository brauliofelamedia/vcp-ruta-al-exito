const { createClient } = require('@supabase/supabase-js');

let supabaseInstance = null;

function getSupabase() {
  if (supabaseInstance) return supabaseInstance;

  const url = process.env.SUPABASE_URL;
  // Usar service role si está disponible (preferido para backend), o anon key
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY || process.env.SUPABASE_ANON_KEY;

  if (!url || !key) {
    throw new Error('Variables de entorno SUPABASE_URL y SUPABASE_SERVICE_ROLE_KEY (o SUPABASE_ANON_KEY) no configuradas.');
  }

  supabaseInstance = createClient(url, key, {
    auth: {
      persistSession: false,
      autoRefreshToken: false,
    },
  });

  return supabaseInstance;
}

module.exports = { getSupabase };
