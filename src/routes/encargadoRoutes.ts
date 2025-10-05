import { Router } from 'express';
import EncargadoController from '../controllers/encargadoController';

const router: Router = Router();

// Registrar encargado
router.post('/encargados/register', EncargadoController.register);

// Obtener listas para dropdowns
router.get('/encargados/options', EncargadoController.options);

export default router;
