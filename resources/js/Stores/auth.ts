import { defineStore } from 'pinia'
import { ref } from 'vue'

interface User {
  id: number;
  email: string;
  role: 'admin' | 'organizer' | 'tabulator' | 'judge';
  name: string;
  assignedPageant?: number;
}

interface UserData extends User {
  password: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = ref(false)

  // Simulated user database
  const users: Record<string, UserData> = {
    'test@admin.com': {
      id: 1,
      email: 'test@admin.com',
      password: 'password123',
      role: 'admin',
      name: 'Admin User'
    },
    'test@organizer.com': {
      id: 2,
      email: 'test@organizer.com',
      password: 'password123',
      role: 'organizer',
      name: 'Event Organizer'
    },
    'test@tabulator.com': {
      id: 3,
      email: 'test@tabulator.com',
      password: 'password123',
      role: 'tabulator',
      name: 'Score Tabulator'
    },
    'test@judge.com': {
      id: 4,
      email: 'test@judge.com',
      password: 'password123',
      role: 'judge',
      name: 'Judge One',
      assignedPageant: 1
    }
  }

  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      if (!email || !password) {
        console.error('Login failed: Email or password is empty')
        return false
      }

      const foundUser = users[email]
      
      if (foundUser && foundUser.password === password) {
        try {
          const { password: _, ...userWithoutPassword } = foundUser
          user.value = userWithoutPassword
          isAuthenticated.value = true
          console.log('Login successful for:', email)
          return true
        } catch (error) {
          console.error('Error processing user data:', error)
          return false
        }
      }
      
      console.log('Login failed: Invalid credentials for', email)
      return false
    } catch (error) {
      console.error('Unexpected login error:', error)
      return false
    }
  }

  const logout = () => {
    user.value = null
    isAuthenticated.value = false
  }

  return {
    user,
    isAuthenticated,
    login,
    logout
  }
})