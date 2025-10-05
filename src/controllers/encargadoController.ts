import { Request, Response } from 'express';
import { EncargadoService } from '../services/encargadoService';
import { EncargadoRegisterRequest, DEPARTAMENTOS, CURSOS, AREAS } from '../types/encargado';

const service = new EncargadoService();

export class EncargadoController {
  async register(req: Request, res: Response) {
    try {
      const body: EncargadoRegisterRequest = req.body;

      // Validaciones simples
      const required = ['nombre', 'apellido', 'ci', 'fecha_nacimiento', 'celular', 'correo', 'contraseña', 'departamento', 'colegio', 'curso', 'area'];
      for (const field of required) {
        if (!Object.prototype.hasOwnProperty.call(body, field) || (body as any)[field] === undefined || (body as any)[field] === '') {
          return res.status(400).json({ success: false, message: `Campo ${field} es requerido` });
        }
      }

      // Email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(body.correo)) {
        return res.status(400).json({ success: false, message: 'Email inválido' });
      }

      // Departamento, curso y area válidos
      if (!DEPARTAMENTOS.includes(body.departamento)) {
        return res.status(400).json({ success: false, message: 'Departamento inválido' });
      }
      if (!CURSOS.includes(body.curso)) {
        return res.status(400).json({ success: false, message: 'Curso inválido' });
      }
      if (!AREAS.includes(body.area)) {
        return res.status(400).json({ success: false, message: 'Area inválida' });
      }

      const result = await service.register(body);

      if (result.error) {
        return res.status(400).json({ success: false, message: result.error });
      }

      return res.status(201).json({ success: true, message: 'Encargado registrado', data: { id: result.id, token: result.token } });
    } catch (error) {
      return res.status(500).json({ success: false, message: 'Error interno del servidor' });
    }
  }

  options(req: Request, res: Response) {
    return res.status(200).json({ success: true, data: { departamentos: DEPARTAMENTOS, cursos: CURSOS, areas: AREAS } });
  }
}

export default new EncargadoController();
