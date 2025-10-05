export type Departamento =
  | 'Chuquisaca'
  | 'La Paz'
  | 'Cochabamba'
  | 'Oruro'
  | 'Potosi'
  | 'Tarija'
  | 'Santa Cruz'
  | 'Beni'
  | 'Pando';

export type Curso =
  | '5to primaria'
  | '6to primaria'
  | '7mo primaria'
  | '8vo primaria'
  | '9no primaria'
  | '1ro secundaria'
  | '2do secundaria'
  | '3ro secundaria'
  | '4to secundaria'
  | '5to secundaria'
  | '6to secundaria';

export type Area =
  | 'Matematica'
  | 'Fisica'
  | 'Quimica'
  | 'Biologia'
  | 'Robotica'
  | 'Astronomia'
  | 'Astrofisica';

export interface EncargadoRegisterRequest {
  nombre: string;
  apellido: string;
  ci: number;
  fecha_nacimiento: string; // ISO date
  celular: string;
  correo: string;
  contraseña: string;
  departamento: Departamento;
  colegio: string;
  curso: Curso;
  area: Area;
}

export interface EncargadoRegisterResponse {
  success: boolean;
  message: string;
  data?: {
    id: string;
    token?: string;
  };
}

export const DEPARTAMENTOS: Departamento[] = [
  'Chuquisaca',
  'La Paz',
  'Cochabamba',
  'Oruro',
  'Potosi',
  'Tarija',
  'Santa Cruz',
  'Beni',
  'Pando',
];

export const CURSOS: Curso[] = [
  '5to primaria',
  '6to primaria',
  '7mo primaria',
  '8vo primaria',
  '9no primaria',
  '1ro secundaria',
  '2do secundaria',
  '3ro secundaria',
  '4to secundaria',
  '5to secundaria',
  '6to secundaria',
];

export const AREAS: Area[] = [
  'Matematica',
  'Fisica',
  'Quimica',
  'Biologia',
  'Robotica',
  'Astronomia',
  'Astrofisica',
];
