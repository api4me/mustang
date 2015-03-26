package main

import (
    "fmt"
    "os"
    "path/filepath"
    "io"
    "io/ioutil"
    "encoding/json"
    "strings"
)
/*
func explorer(path string, info os.FileInfo, err error) error {
    if err != nil {
        fmt.Printf("ERROR: %v", err)
        return err
    }

    if info.IsDir() {
        fmt.Printf("Folder: %s\n", path)
    } else {
        fmt.Printf("File: %s\n", path)
    }

    return nil
}

func main() {
    filepath.Walk("data/pic/", explorer)
}
*/
func CopyFile(src, dst string) (int64, error) {
    sf, err := os.Open(src)
    if err != nil {
        return 0, err
    }
    defer sf.Close()

    df, err := os.OpenFile(dst, os.O_WRONLY|os.O_CREATE, 0644)
    if err != nil {
        return 0, err
    }
    defer df.Close()

    return io.Copy(df, sf)
}

func main() {
    var data interface{}
    source := "data/pic.json"
    buff, err := ioutil.ReadFile(source)
    if err != nil {
        fmt.Printf("Error: Read file %s failed\n", source)
        os.Exit(1)
    }

    err = json.Unmarshal(buff, &data)
    if err != nil {
        fmt.Print("Error: Source is not a json file\n")
        os.Exit(1)
    }
    // fmt.Printf("%v", data)
    m := data.(map[string]interface{})

    path := "data/pic/"
    idx := 1000
    filepath.Walk(path, func(path string, info os.FileInfo, err error) error {
        if err != nil  {
            return err
        }

        if !info.IsDir() {
            // fmt.Printf("%s\n", path)
            // data/pic/凉菜/b/养生虫草花.jpg
            arr := strings.Split(path,  "/")
            if len(arr) != 5 {
                fmt.Printf("!! The file struct is not corrent\n")
                return  nil
            }
            if arr[3] == "b" {
                // SQL Format
                // Get dish is
                name := strings.Split(arr[4], ".")[0]
                m1 := (m[arr[2]]).(map[string]interface{})
                var did interface{}
                for _, val := range strings.Split(name, "-") {
                    n := m1[val]
                    if n != nil {
                        did = n
                        break
                    }
                }
                if did == nil {
                    fmt.Printf("/* Error: No file name: %s is not match the dish table. */\n", path)
                    return nil
                }

                file := arr[2] + "-" + arr[4]
                to := fmt.Sprintf("assets/upload/%s", file)
                q := "INSERT INTO DISH_PICTURE(PIC_OID, PIC_NAME, PIC_URL, IS_DFLT, IS_DISP, DISH_OID) VALUES(%d, '%s', '%s', 'n', 'y', %s);\n";
                fmt.Printf(q, idx, arr[4], to, did)
                // Move file to assets upload
                CopyFile(path, "../" + to)
                arr[3] = "s"
                thumb := strings.Join(arr, "/")
                file = strings.Replace(file, ".", "_i.", 1) 
                to = fmt.Sprintf("assets/upload/%s", file)
                CopyFile(thumb, "../" + to)

                idx++
            }
        }

        return nil
    })
}
