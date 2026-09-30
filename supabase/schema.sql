-- ==========================================================
-- VCP · Ruta del Éxito · Esquema de Base de Datos para Supabase
-- ==========================================================

-- Habilitar extensión para UUID si no está activa
create extension if not exists "pgcrypto";

-- 1. Tabla de Estudiantes
create table if not exists public.students (
    id uuid default gen_random_uuid() primary key,
    email text unique not null,
    full_name text,
    system text default 'medium' check (system in ('medium', 'elite')),
    residence text default 'usa' check (residence in ('usa', 'outside')),
    ghl_contact_id text,
    metadata jsonb default '{}'::jsonb,
    created_at timestamptz default now() not null,
    last_active_at timestamptz default now() not null
);

-- 2. Tabla de Progreso (marcas de tareas por estudiante)
create table if not exists public.student_progress (
    id bigint generated always as identity primary key,
    student_id uuid references public.students(id) on delete cascade not null,
    task_key text not null, -- formato: 'sistema:estacion:id_tarea' ej: 'medium:0:academia'
    completed boolean default true not null,
    completed_at timestamptz default now() not null,
    constraint unique_student_task unique (student_id, task_key)
);

-- 3. Índices para máximo rendimiento
create index if not exists idx_students_email on public.students (lower(email));
create index if not exists idx_student_progress_student on public.student_progress (student_id);
create index if not exists idx_student_progress_task on public.student_progress (student_id, task_key);

-- 4. Seguridad de Nivel de Fila (RLS)
alter table public.students enable row level security;
alter table public.student_progress enable row level security;

-- Política permisiva para lecturas y escrituras anónimas si se usa la clave pública/anon
-- (Recomendado: Usar la clave de servicio en Vercel Serverless para máxima seguridad)
create policy "Permitir lectura publica de estudiantes por email"
    on public.students for select
    using (true);

create policy "Permitir insercion/actualizacion de estudiantes"
    on public.students for all
    using (true)
    with check (true);

create policy "Permitir lectura de progreso"
    on public.student_progress for select
    using (true);

create policy "Permitir insercion/actualizacion de progreso"
    on public.student_progress for all
    using (true)
    with check (true);

-- Comentarios explicativos
comment on table public.students is 'Estudiantes de VendeComoPro en la Ruta del Éxito';
comment on table public.student_progress is 'Registro de tareas marcadas y completadas por cada estudiante';
