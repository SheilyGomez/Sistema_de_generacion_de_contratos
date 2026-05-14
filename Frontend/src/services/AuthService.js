import api from '../api/axios';

export default {
    
    register(userData) {
        // ruta de la api 
        return api.post('/auth/register', userData);
    },


    login(credentials) {
        return api.post('/auth/login', credentials);
    }

}