export interface User {
  id: string;
  email: string;
  role: 'administrador' | 'encargado' | 'olimpista';
  nombre: string;
  apellido: string;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: User | null;
  token: string | null;
  error?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
  nombre: string;
  apellido: string;
  role: 'administrador' | 'encargado' | 'olimpista';
}

export interface JwtPayload {
  userId: string;
  email: string;
  role: string;
  iat: number;
  exp: number;
}
