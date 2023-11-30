<template>
  <div class="hello">
    <form
      id="form"
      @submit.prevent="submitForm"
      method="post">
      
      <p v-if="errors.length" class="error-message">
        <b>Proszę popraw błędy:</b>
        <ul>
          <li v-for="error in errors">{{ error }}</li>
        </ul>
      </p>

      <p>
        <label for="firstname">Imię:</label>
        <input
          id="firstname"
          v-model="formData.firstname"
          type="text"
          name="firstname"
          placeholder="podaj Imię">
      </p>

      <p>
        <label for="surname">Nazwisko:</label>
        <input
          id="surname"
          v-model="formData.surname"
          type="text"
          name="surname"
          placeholder="podaj nazwisko">
      </p>

      <p>
        <label for="email">Email:</label>
        <input
          id="email"
          v-model="formData.email"
          type="email"
          name="email"
          placeholder="podaj email">
      </p>

      <p>
        <label for="text">Wiadomość:</label>
        <input 
          id="text"
          v-model="formData.text"
          type="text"
          name="text"
          placeholder="napisz wiadomość">
      </p>
      
      <button type="submit">Wyślij</button>
    </form>
  
    <button @click="fetchData">Pobierz wszystkie dane</button>

    <div v-if="requestMessage" class="request-message">
      {{ requestMessage }}
    </div>

    <table v-if="responseData">
      <thead>
        <tr>
          <th>Imię</th>
          <th>Nazwisko</th>
          <th>Email</th>
          <th>Wiadomość</th>
          <th>Data wysłania</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!showAllData">
          <td>{{ responseData.firstname }}</td>
          <td>{{ responseData.surname }}</td>
          <td>{{ responseData.email }}</td>
          <td>{{ responseData.text }}</td>
          <td>{{ responseData.submissionDate }}</td>
        </tr>
        <tr v-for="item in responseData" :key="item.id">
          <td>{{ item.firstName }}</td>
          <td>{{ item.lastName }}</td>
          <td>{{ item.email }}</td>
          <td>{{ item.message }}</td>
          <td>{{ item.submissionDate }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FormComponent',
  data() {
    return {
      formData: {
        firstname: '',
        surname: '',
        email: '',
        text: ''
      },
      errors: [],
      requestMessage: '',
      responseData: null,
      showAllData: false
    };
  },
  methods: {
    async submitForm() {
      this.errors = [];
      this.requestMessage = null;
      this.responseData = null;

      if (!this.formData.firstname) {
        this.errors.push('Imię wymagane');
      }
      if (!this.formData.surname) {
        this.errors.push('Nazwisko wymagane');
      }
      if (!this.formData.email) {
        this.errors.push('Email wymagany');
      } else if (!this.validEmail(this.formData.email)) {
        this.errors.push('Nieprawidłowy email');
      }
      if (!this.formData.text) {
        this.errors.push('Wiadomość wymagana');
      }

      if (this.errors.length === 0) {
        this.formData.submissionDate = new Date().toISOString();

        try {
          const response = await axios.post(
            "http://localhost:8000/contact/create",
            this.formData,
            {
              headers: {
                'Content-Type': 'application/json'
              }
            }
          );

          this.responseData = JSON.parse(JSON.stringify(response.data.data));
          
          this.showAllData = false;
          this.requestMessage = "Dane przesłane na backend";
        } catch (error) {
          this.requestMessage = "Wystąpił błąd podczas wysyłania formularza.";
        }
      }
    },

    validEmail: function (email) {
      var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
    },

    async fetchData() {
      try {
        const response = await axios.get('http://localhost:8000/contacts');
        console.log('Dane pobrane z backendu: ', response.data);

        this.responseData = JSON.parse(response.data.contacts);
        
        this.showAllData = true;
        this.requestMessage = "Wszystkie dane pobrane z backendu";
      } catch (error) {
        console.error('Błąd podczas pobierania danych z backendu: ', error);
      }
    }
  }
};
</script>

<style scoped>
.error-message {
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 5px;
}

.request-message {
  background-color: #d1ecf1;
  border: 1px solid #bee5eb;
  color: #0c5460;
  padding: 10px;
  margin-top: 15px;
  margin-bottom: 15px;
  border-radius: 5px;
}

.hello {
  display: flex;
  flex-direction: column;
  align-items: center;
}

form {
  width: 40%;
  align-items: center;
}

form label {
  display: inline-block;
  width: 100px;
  text-align: justify;
}

form input {
  width: 200px;
}

table {
  width: 80%;
  border-collapse: collapse;
  margin-top: 10px;
}

table, th, td {
  border: 1px solid #ddd;
}

th, td {
  padding: 10px;
  text-align: left;
}

th {
  background-color: #f2f2f2;
}

button {
  margin: 4px;
}
</style>
