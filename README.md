## LazyWorktree ##

###  ⚠️ STILL VERY MUCH WORK IN PROGRESS ⚠️ ###

#### What is it?
A simple TUI to manage GIT Worktrees. It was written in PHP using the [Laravel Prompts Library](https://github.com/laravel/prompts).

### Prerequisites
- GIT
- PHP >= 8.2
- Composer
- Linux (Not tested on other platforms)


#### Installation
``` bash
git clone git@github.com:alexeightsix/lazyworktree.git ~/.local/share/lazyworktree
cd ~/.local/share/lazyworktree
composer install --no-dev
sudo chmod a+x ~/.local/share/lazyworktree/bin
sudo ln -s ~/.local/share/lazyworktree/bin /usr/local/bin/lazyworktree
```

#### Shell Alias (eg: .bashrc, .zshrc)
``` bash
export lw='lazyworktree'
```

#### Usage
``` bash
mkdir /my_repo 
cd /my_repo
lazyworktree init
```

### Initalize TUI
``` bash
lazyworktree 
```

### Shortcuts
``` bash
lazyworktree add
lazyworktree delete
lazyworktree switch
```

### Current Symlink
A ```current``` folder will be created in the root directory of your project linking to the active worktree. This is useful when your project is setup with Docker and volume mounts.

### IDE Integration
If you're creating a plugin for your IDE you can use the following commands to interact with the TUI
``` bash
lazyworktree api list
lazyworktree api switch <baseName|path>
```

### Hooks 
Execute a shell script before or after an action is performed. The file must exist in either the root of your project or worktree root.
```
hook_before_switch.sh
hook_after_switch.sh

hook_before_create.sh
hook_after_create.sh

hook_before_delete.sh
hook_after_delete.sh
```

### Neovim Integration with Telescope
```lua
vim.keymap.set('n', '<leader>lw', function()
  require("plugins.lwt").switch()
end)
```

```lua 
local M = {}

M.exec_get_string = function(cmd)
  local f = assert(io.popen(cmd, 'r'))
  local s = assert(f:read('*a'))
  f:close()
  s = string.gsub(s, '^%s+', '')
  s = string.gsub(s, '%s+$', '')
  s = string.gsub(s, '[\n\r]+', ' ')
  return s
end

M.is_success = function(cmd)
  local code = os.execute(cmd .. " > /dev/null 2>&1")
  return code == 0
end

M.checkhealth = function()
  local binary = false
  local health = false
  local ok = M.is_success("which lazyworktree")
  if ok then
    binary = true
  end

  local health = M.is_success("lazyworktree api health")

  if health then
    health = true
  end
  return binary, health
end

M.list_worktrees = function()
  local res = M.exec_get_string("lazyworktree api list")
  local json = vim.fn.json_decode(res)
  local worktrees = {}

  for _, v in pairs(json) do
    table.insert(worktrees, v)
  end

  return worktrees
end

M.api_switch = function(worktree)
  M.exec_get_string("lazyworktree api switch" .. " " .. worktree.value.path)
  vim.api.nvim_set_current_dir(worktree.value.path)
  vim.notify("Switched to " .. worktree.value.baseName)
end

M.load_telescope = function()
  local action_state = require "telescope.actions.state"
  local actions = require "telescope.actions"
  local dropdown = require("telescope.themes").get_dropdown {}
  local finders = require "telescope.finders"
  local pickers = require "telescope.pickers"
  return action_state, actions, dropdown, finders, pickers
end

M.switch = function()
  local binary, is_worktree = M.checkhealth()

  if not binary then
    vim.notify("lazyworktree binary not found")
    return
  end

  if not is_worktree then
    vim.notify("lazyworktree api not found")
    return
  end

  local action_state, actions, dropdown, finders, pickers = M.load_telescope()

  pickers.new(dropdown, {
    prompt_title = "Switch to Worktree",
    finder = finders.new_table {
      results = M.list_worktrees(),
      entry_maker = function(entry)
        return {
          value = entry,
          display = entry.baseName,
          ordinal = entry.baseName,
        }
      end
    },
    attach_mappings = function(prompt_bufnr)
      actions.select_default:replace(function()
        actions.close(prompt_bufnr)
        M.api_switch(action_state.get_selected_entry())
      end)
      return true
    end,
  }):find()
end

return M;
```
