<template>
  <div class="app-container">
    <div class="filter-container" style="margin-bottom: 20px">
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">
        Create
      </el-button>
    </div>
    <el-table
      v-loading="listLoading"
      :data="list"
      element-loading-text="Loading"
      border
      fit
      highlight-current-row
    >
      <el-table-column align="center" label="ID" width="95">
        <template slot-scope="scope">
          {{ scope.row.id }}
        </template>
      </el-table-column>
      <el-table-column label="AppKey" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.app_key }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Additional Script" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.html }}</span>
        </template>
      </el-table-column>
      <el-table-column label="AppSecret" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.app_secret }}</span>
        </template>
      </el-table-column>
      <el-table-column label="SignKey" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.sign_key }}</span>
        </template>
      </el-table-column>

      <el-table-column label="ShopifyDomain" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.shopify_domain }}</span>
        </template>
      </el-table-column>
<!--      <el-table-column label="ShopifyShopName" width="220" align="center">-->
<!--        <template slot-scope="scope">-->
<!--          <span>{{ scope.row.shopify_shop_name }}</span>-->
<!--        </template>-->
<!--      </el-table-column>-->
      <el-table-column label="ShopifyApiKey" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.shopify_api_key }}</span>
        </template>
      </el-table-column>
      <el-table-column label="ShopifyApiSecret" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.shopify_api_secret }}</span>
        </template>
      </el-table-column>
      <el-table-column label="ShopifyAccessToken" width="220" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.shopify_access_token }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="CreateTime" width="200">
        <template slot-scope="scope">
          <span>{{ scope.row.create_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="230" class-name="small-padding fixed-width">
        <template slot-scope="{row}">
          <el-button type="primary" size="mini" @click="handleUpdate(row)">
            Edit
          </el-button>
          <el-button size="mini" type="danger" @click="handleDelete(row)">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" :model="temp" label-position="left" label-width="200px" style="width: 600px; margin-left:50px;">
        <el-form-item label="AppKey">
          <el-input v-model="temp.app_key" />
        </el-form-item>
        <el-form-item label="AppSecret">
          <el-input v-model="temp.app_secret" />
        </el-form-item>
        <el-form-item label="SignKey">
          <el-input v-model="temp.sign_key" />
        </el-form-item>
        <el-form-item label="ShopifyDomain">
          <el-input v-model="temp.shopify_domain" />
        </el-form-item>
<!--        <el-form-item label="ShopifyShopName">-->
<!--          <el-input v-model="temp.shopify_shop_name" />-->
<!--        </el-form-item>-->
        <el-form-item label="ShopifyApiKey">
          <el-input v-model="temp.shopify_api_key" />
        </el-form-item>
        <el-form-item label="ShopifyApiSecret">
          <el-input v-model="temp.shopify_api_secret" />
        </el-form-item>
        <el-form-item label="ShopifyAccessToken">
          <el-input v-model="temp.shopify_access_token" />
        </el-form-item>
        <el-form-item>
          <el-button @click="dialogFormVisible = false">
            Cancel
          </el-button>
          <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
            Confirm
          </el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import { appList, createApp, delApp, editApp } from '@/request/api'

export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        published: 'success',
        draft: 'gray',
        deleted: 'danger'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      list: null,
      listLoading: true,
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: 'Edit',
        create: 'Create'
      },
      dialogPvVisible: false,
      temp: {
        id: undefined,
        app_key: '',
        app_secret: '',
        sign_key: '',
        shopify_domain: '',
        // shopify_shop_name: '',
        shopify_api_key: '',
        shopify_api_secret: '',
        shopify_access_token: ''
      }
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.listLoading = true
      const resl = await appList()
      if (resl.code === 1) {
        this.list = resl.data
        this.listLoading = false
      }
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        app_key: '',
        app_secret: '',
        sign_key: '',
        shopify_domain: '',
        // shopify_shop_name: '',
        shopify_api_key: '',
        shopify_api_secret: '',
        shopify_access_token: ''
      }
    },
    handleCreate() {
      this.resetTemp()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    async createData() {
      const resc = await createApp(this.temp)
      if (resc.code === 1) {
        // this.list.unshift(resc.data)
        this.dialogFormVisible = false
        this.$notify({
          title: 'Success',
          message: 'Created Successfully',
          type: 'success',
          duration: 2000,
          onClose: () => {
            window.location.reload()
          }
        })
      } else {
        this.$message({
          message: resc.msg,
          type: 'error'
        })
      }
      // this.$refs['dataForm'].validate((valid) => {
      //   if (valid) {
      //     createArticle(this.temp).then(() => {
      //       this.list.unshift(this.temp)
      //       this.dialogFormVisible = false
      //       this.$notify({
      //         title: 'Success',
      //         message: 'Created Successfully',
      //         type: 'success',
      //         duration: 2000
      //       })
      //     })
      //   }
      // })
    },
    handleUpdate(row) {
      this.temp = Object.assign({}, row) // copy obj
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    async updateData() {
      const resc = await editApp(this.temp)
      if (resc.code === 1) {
        this.dialogFormVisible = false
        this.$notify({
          title: 'Success',
          message: 'Updated Successfully',
          type: 'success',
          duration: 2000,
          onClose: () => {
            window.location.reload()
          }
        })
      } else {
        this.$message({
          message: resc.msg,
          type: 'error'
        })
      }
    },
    async handleDelete(row, index) {
      const resc = await delApp(row)
      if (resc.code === 1) {
        this.$notify({
          title: 'Success',
          message: 'Delete Successfully',
          type: 'success',
          duration: 2000,
          onClose: () => {
            window.location.reload()
          }
        })
      } else {
        this.$message({
          message: resc.msg,
          type: 'error'
        })
      }
    }
  }
}
</script>
