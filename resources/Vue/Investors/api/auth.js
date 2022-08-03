import axios from 'axios';
const config = {
  baseURL: '/api/auth'
};
export default axios.create(config);
// for non authorized requests