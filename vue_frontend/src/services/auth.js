const API_BASE_URL = 'http://localhost:8000/api'; // Thay đổi URL này theo cấu hình của bạn

/**
 * Service để xử lý authentication
 */
class AuthService {
  /**
   * Đăng nhập
   */
  async login(email, password) {
    try {
      const response = await fetch(`${API_BASE_URL}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include', // Quan trọng: gửi cookies cho session
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();
      
      if (data.success) {
        // Lưu thông tin user vào localStorage
        localStorage.setItem('user', JSON.stringify(data.user));
        return { success: true, user: data.user, message: data.message };
      } else {
        return { success: false, message: data.message || 'Đăng nhập thất bại' };
      }
    } catch (error) {
      console.error('Login error:', error);
      return { success: false, message: 'Có lỗi xảy ra khi đăng nhập' };
    }
  }

  /**
   * Đăng ký
   */
  async register(userData) {
    try {
      const response = await fetch(`${API_BASE_URL}/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(userData),
      });

      const data = await response.json();
      
      if (data.success) {
        localStorage.setItem('user', JSON.stringify(data.user));
        return { success: true, user: data.user, message: data.message };
      } else {
        return { 
          success: false, 
          message: data.message || 'Đăng ký thất bại',
          errors: data.errors || {}
        };
      }
    } catch (error) {
      console.error('Register error:', error);
      return { success: false, message: 'Có lỗi xảy ra khi đăng ký' };
    }
  }

  /**
   * Đăng xuất
   */
  async logout() {
    try {
      await fetch(`${API_BASE_URL}/auth/logout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include',
      });

      localStorage.removeItem('user');
      return { success: true };
    } catch (error) {
      console.error('Logout error:', error);
      localStorage.removeItem('user');
      return { success: true }; // Vẫn xóa local storage dù API fail
    }
  }

  /**
   * Kiểm tra user hiện tại
   */
  async checkAuth() {
    try {
      const response = await fetch(`${API_BASE_URL}/auth/me`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include',
      });

      const data = await response.json();
      
      if (data.success) {
        localStorage.setItem('user', JSON.stringify(data.user));
        return { success: true, user: data.user };
      } else {
        localStorage.removeItem('user');
        return { success: false };
      }
    } catch (error) {
      console.error('Check auth error:', error);
      return { success: false };
    }
  }

  /**
   * Lấy user từ localStorage
   */
  getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  }

  /**
   * Kiểm tra đã đăng nhập chưa
   */
  isAuthenticated() {
    return this.getCurrentUser() !== null;
  }

  /**
   * Kiểm tra có phải admin không
   */
  isAdmin() {
    const user = this.getCurrentUser();
    return user && user.role === 'admin';
  }
}

export default new AuthService();

