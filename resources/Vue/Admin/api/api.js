import axios from 'axios';
const config = {
  baseURL: '/api/admin'
};
export default axios.create(config);
// for non authorized requests