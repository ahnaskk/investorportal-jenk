import axios from 'axios';
const config = {
  baseURL: '/api/admin'
};
export default axios.create(config);
// interceptors are set in app.js
/*
  Even though both of the API instances look similar at the floor,
  they are used in different ways. 'free' instance doesn't get
  assigned any authorization tokens on the fly. But this one does.
*/
